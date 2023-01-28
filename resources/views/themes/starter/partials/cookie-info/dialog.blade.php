<div class="modal fade" id="cookie-info" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">{{ __('labels.generic.cookies_privacy') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-cookie-bite fa-4x text-secondary"></i>
                    </div>

                    <div class="col-9">
                        <p>{{ __('messages.generic.cookie_info') }}
                        <a class="d-block" target="_blank" href="https://gdpr.eu/cookies">{{ __('labels.generic.read_more') }}</a></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="js-cookie-info-checked btn btn-outline-dark" data-bs-dismiss="modal">
                    {{ __('labels.button.reject') }}
                </button>
                <button type="button" class="js-cookie-info-checked btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('labels.button.accept') }}
                </button>
            </div>
        </div>
    </div>
</div>
