<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\AppMailer;
use Illuminate\Support\Facades\Mail;
use App\Traits\CheckInCheckOut;
use App\Traits\Translatable;
use App\Traits\OptionList;
use Illuminate\Http\Request;


class Email extends Model
{
    use HasFactory, CheckInCheckOut, Translatable, OptionList;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'plain_text',
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
    public function delete()
    {
        $code = $this->code;
        $this->translations()->delete();

        parent::delete();

	// Delete template files associated with the model.

        foreach (config('app.locales') as $locale) {
            if (file_exists(resource_path().'/views/emails/'.$locale.'-'.$code.'.blade.php')) {
                unlink(resource_path().'/views/emails/'.$locale.'-'.$code.'.blade.php');
                unlink(resource_path().'/views/emails/'.$locale.'-'.$code.'-plain.blade.php');
            }
        }
    }

    /*
     * Creates or updates the template files associated with the model.
     */
    public function setViewFiles($locale)
    {
        if (!file_exists(resource_path().'/views/emails')) {
	    mkdir(resource_path().'/views/emails', 0755, true);
	}

        // Name the email template after the code attribute.
	$html = resource_path().'/views/emails/'.$locale.'-'.$this->code.'.blade.php';
	$text = resource_path().'/views/emails/'.$locale.'-'.$this->code.'-plain.blade.php';

	file_put_contents($html, $this->getTranslation($locale)->body_html);
	file_put_contents($text, $this->getTranslation($locale)->body_text);
    }

    /*
     * Gets the email items according to the filter, sort and pagination settings.
     */
    public static function getEmails(Request $request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);

        $query = Email::select('emails.*', 'translations.subject as subject')
            ->join('translations', function ($join) use($search) { 
                $join->on('emails.id', '=', 'translatable_id')
                    ->where('translations.translatable_type', Email::class)
                    ->where('locale', '=', config('app.locale'));
        });

	//$query = Email::query();

	if ($search !== null) {
	    $query->where('emails.code', 'like', '%'.$search.'%');
	}

	if ($sortedBy !== null) {
	    preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
	    $query->orderBy($matches[1], $matches[2]);
	}

        return $query->paginate($perPage);
    }

    public static function getEmail($id, $locale)
    {
        return Email::select('emails.*','users.name as modifier_name',
                             'translations.subject as subject',
                             'translations.body_html as body_html',
                             'translations.body_text as body_text')
            ->leftJoin('users as users', 'emails.updated_by', '=', 'users.id')
            ->leftJoin('translations', function ($join) use($locale) { 
                $join->on('emails.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', Email::class)
                     ->where('locale', '=', $locale);
        })->findOrFail($id);
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

    public static function sendTestEmail()
    {
        $data = auth()->user();
        $data->subject = 'Starter CMS - Test email';
	$data->view = 'emails.'.config('app.locale').'-user-registration';

        try {
            Mail::to($data->email)->send(new AppMailer($data));
            return true;
        }
        catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /*
     * Send an email through a given email template.
     * @param  string  $code
     * @param  mixed  data$
     * @return boolean
     */
    public static function sendEmail(string $code, mixed $data, string $locale): bool
    {
        if(!$email = Email::where('code', $code)->first()) {
            report('Warning: Email object with code: "'.$code.'" not found.');
            return false;
        }

        $plain = ($email->plain_text) ? '-plain' : '';

        // Check if the view for the current locale exists or use the fallback locale.
        $locale = (file_exists(resource_path().'/views/emails/'.$locale.'-'.$code.$plain.'.blade.php')) ? $locale : config('app.fallback_locale');

        // Check the possibly view for the fallback locale.
        if ($locale == config('app.fallback_locale') && !file_exists(resource_path().'/views/emails/'.$locale.'-'.$code.$plain.'.blade.php')) {
            report('Warning: Email view: "'.$locale.'-'.$code.$plain.'" not found.');
            return false;
        }

        $translation = $email->getTranslation($locale, true);
	$data->subject = self::parseSubject($translation->subject, $data);

        $recipients = [];

        // Check for a recipient email array.
        if (isset($data->recipients)) {
            $recipients = $data->recipients;
        }
        else {
            // Use the email attribute as recipient in case the recipient attribute doesn't exist.
            $recipients[] = (!isset($data->recipient) && isset($data->email)) ? $data->email : $data->recipient;
        }

	$data->view = 'emails.'.$locale.'-'.$code;

        try {
            Mail::to($recipients)->send(new AppMailer($data));
            return true;
        }
        catch (\Throwable $e) {
            report($e);
            return false;
        }

        // Alternative possibility
        /*dispatch(function() use($recipient, $data) {
            Mail::to($recipient)->send(new AppMailer($data));
        })->afterResponse();*/
    }

    /*
     * Replaces the possibles variable set in the email subject with their values.
     * @param  string  $subject
     * @param  mixed  data$
     * @return string
     */
    public static function parseSubject(string $subject, mixed $data): string
    {
        // Looks for Blade variables (eg: {{ $data->email }}).
        if (preg_match_all('#{{\s?[\$a-zA-Z0-9\-\>]+\s?}}#U', $subject, $matches)) {
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
