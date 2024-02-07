<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\AppMailer;
use Illuminate\Support\Facades\Mail;
use App\Traits\CheckInCheckOut;
use App\Traits\OptionList;
use App\Models\Cms\Setting;
use Illuminate\Http\Request;
use App\Jobs\SendEmail;
use App\Jobs\SendTestEmail;

class Email extends Model
{
    use HasFactory, CheckInCheckOut, OptionList;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'subject',
        'body_html',
        'body_text',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'checked_out_time'
    ];


    /*
     * Override.
     */
    public function save(array $options = [])
    {
        // Replace the HTML entities set by the editor in the code placeholders (eg: {{ $data-&gt;name }}).
	$this->body_html = preg_replace('#({{[\s\$a-zA-Z0-9_]+)-&gt;([a-zA-Z0-9_\s]+}})#', '$1->$2', $this->body_html);

        parent::save($options);

	$this->setViewFiles();
    }

    /*
     * Override.
     */
    public function delete()
    {
        $code = $this->code;

        parent::delete();

	// Delete template files associated with the model.
	unlink(resource_path().'/views/emails/'.$code.'.blade.php');
	unlink(resource_path().'/views/emails/'.$code.'-plain.blade.php');
    }

    /*
     * Creates or updates the template files associated with the model.
     */
    public function setViewFiles()
    {
        if (!file_exists(resource_path().'/views/emails')) {
	    mkdir(resource_path().'/views/emails', 0755, true);
	}

        // Name the email template after the code attribute.
	$html = resource_path().'/views/emails/'.$this->code.'.blade.php';
	$text = resource_path().'/views/emails/'.$this->code.'-plain.blade.php';

	file_put_contents($html, $this->body_html);
	file_put_contents($text, $this->body_text);
    }

    /*
     * Gets the email items according to the filter, sort and pagination settings.
     */
    public static function getEmails($request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);

	$query = Email::query();

	if ($search !== null) {
	    $query->where('code', 'like', '%'.$search.'%');
	}

	if ($sortedBy !== null) {
	    preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
	    $query->orderBy($matches[1], $matches[2]);
	}

        return $query->paginate($perPage);
    }

    /*
     * Builds the options for the 'format' select field.
     */
    public function getFormatOptions()
    {
        return [
	    ['value' => 'plain_text', 'text' => 'Plain text'], 
	    ['value' => 'html', 'text' => 'HTML']
	];
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue(\stdClass $field): mixed
    {
        if ($field->name == 'format') {
	    return ($this->plain_text) ? 'plain_text' : 'html';
	}

        return $this->{$field->name};
    }

    public static function sendTestEmail(): bool
    {
        // Get the current user.
        $user = auth()->user();
        // Prepare the email data.
        $data = new \stdClass;
        $data->subject = 'Starter CMS - Test email';
	$data->view = 'emails.user-registration';
        $data->email = $user->email;
        $data->name = $user->name;

        if (Setting::getValue('website', 'email_sending_method') == 'synchronous') {
            try {
                Mail::to($data->email)->send(new AppMailer($data));
            }
            catch (\Throwable $e) {
                report($e);
                return false;
            }
        }
        // asynchronous
        else {
            // Send job to queue.
            SendTestEmail::dispatch($data);
        }

        return true;
    }

    /*
     * Send one or more emails through a given email template.
     * @param  string  $code
     * @param  object  $data
     * @return boolean
     */
    public static function sendEmail(string $code, object $data): bool
    {
        // Check first for code and view.

	if(!$email = Email::where('code', $code)->first()) {
            report('Warning: Email object with code: "'.$code.'" not found.');
            return false;
        }

        if (!file_exists(resource_path().'/views/emails/'.$code.'.blade.php')) {
            report('Warning: Email view: "'.$code.'" not found.');
            return false;
        }

	$data->view = 'emails.'.$code;
	$data->subject = self::parseSubject($email->subject, $data);

        $recipients = [];

        // Check for a recipient email array.
        if (isset($data->recipients)) {
            $recipients = $data->recipients;
        }
        else {
            // Use the email attribute as recipient in case the recipient attribute doesn't exist.
            $recipients[] = (!isset($data->recipient) && isset($data->email)) ? $data->email : $data->recipient;
        }

        if (Setting::getValue('website', 'email_sending_method') == 'synchronous') {
            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient)->send(new AppMailer($data));
                }
                catch (\Throwable $e) {
                    report($e);
                    return false;
                }
            }
        }
        // asynchronous
        else {
            // Send job to queue.
            SendEmail::dispatch($data, $recipients);
        }

        return true;
    }

    /*
     * Replaces the possibles variable set in the email subject with their values.
     * @param  string  $subject
     * @param  mixed  data$
     * @return string
     */
    private static function parseSubject(string $subject, mixed $data): string
    {
        // Looks for Blade variables (eg: {{ $data->email }}).
        if (preg_match_all('#{{\s?[\$a-zA-Z0-9_\-\>]+\s?}}#U', $subject, $matches)) {
	    $results = $matches[0];
	    $patterns = $replacements = [];

	    foreach ($results as $result) {
	        // Gets the attribute name (eg: email).
		preg_match('#^{{\s?\$[a-zA-Z0-9_]+->([a-zA-Z0-9_]+)\s?}}$#', $result, $matches);
		$attribute  = $matches[1];
		// Stores the variable value.
		$replacements[] = $data->$attribute;
		// Stores the corresponding Blade variable.
		$patterns[] = '#({{\s?\$[a-zA-Z0-9_]+->'.$attribute.'\s?}})#';
	    }

	    return preg_replace($patterns, $replacements, $subject);
	}

	return $subject;
    }
}
