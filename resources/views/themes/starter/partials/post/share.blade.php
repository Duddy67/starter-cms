<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel">@lang ('labels.generic.share_this_page')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-2" style="cursor: pointer;" onclick="share('facebook')">
              <div class="border-bottom">
                  <i class="bi-facebook h2 text-primary me-2" aria-hidden="true"></i>
                  <span class="align-top">@lang ('labels.generic.share_on') <span class="fw-bold text-secondary">Facebook</span></span>
              </div>
          </div>
          <div class="mb-2" style="cursor: pointer;" onclick="share('bluesky')">
              <div class="border-bottom">
                  <i class="bi-bluesky h2 text-primary me-2" aria-hidden="true"></i>
                  <span class="align-top">@lang ('labels.generic.share_on') <span class="fw-bold text-secondary">Bluesky</span></span>
              </div>
          </div>
          <div class="mb-2" style="cursor: pointer;" onclick="share('twitter')">
              <div class="border-bottom">
                  <i class="bi-twitter-x h4 me-3" aria-hidden="true"></i>
                  <span class="align-top">@lang ('labels.generic.share_on') <span class="fw-bold text-secondary">Twitter X</span></span>
              </div>
          </div>
          <div class="mb-1 mt-4" onclick="copyToClipboard()">
              <div id="copyUrl" class="btn btn-secondary mb-2">
                  @lang ('labels.generic.copy_url')
              </div>
              <div id="copiedToClipboard" class="mb-1" style="display: none;">
                  @lang ('labels.generic.url_copied_into_clipboard')
              </div>
              <div>
                  <input type="text" id="urlToShare" class="form-control" value="{{ url()->current() }}" />
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang ('labels.generic.close')</button>
      </div>
    </div>
  </div>
</div>

<script>
    function copyToClipboard() {
        // Get the url input element.
        const url = document.getElementById('urlToShare');
        // Select the text field.
        url.select();
        // For mobile devices.
        url.setSelectionRange(0, 99999);
        // Copy the url inside the text field.
        // Note: An error occurs when no served with https:
        // Cannot read properties of undefined reading 'writeText'.
        navigator.clipboard.writeText(url.value);

        // Show the confirmation message.
        document.getElementById('copiedToClipboard').style.display = 'block';
    }

    function share(social) {
        const networks = {
            facebook: "https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}",
            bluesky: "https://bsky.app/intent/compose?text={{ urlencode(url()->current()) }}",
            twitter: "https://x.com/intent/tweet?text=Laravel Package tutorial&url={{urlencode(url()->current())}}",
        };

        // Open the social network page in a new tab.
        window.open(networks[social], '_blank').focus();
    }
</script>

