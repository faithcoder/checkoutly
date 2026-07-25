<div id="jwcfe-feedback-popup" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(95%,520px);background:#fff;padding:1.5em;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.15);z-index:99999;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,sans-serif;width:48%">
    <style>
        .jwcfe-modal-header {margin:0 0 1em;padding-bottom:1em;border-bottom:1px solid #ddd}
        .jwcfe-modal-title {font-size:1.3em;color:#1d2327;margin:0}
        .jwcfe-form-group {margin-bottom:0.8em}
        .jwcfe-radio-label {display:flex;align-items:flex-start;gap:8px;padding:8px;border-radius:4px;transition:background 0.2s;cursor:pointer;font-size:14px}
        .jwcfe-radio-label:hover {background:#f6f7f7}
        .jwcfe-radio-input {width:16px;height:16px;accent-color:#3858e9;margin:0;margin-top:4px !important;flex-shrink:0;}
        .jwcfe-textarea {width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;min-height:90px;resize:vertical;font-size:14px;margin-top:8px;}
        .jwcfe-textarea::placeholder{color:#9f9fa0;}
        .jwcfe-textarea:focus {outline:none;border-color:#3858e9;box-shadow:0 0 0 2px rgba(56,88,233,0.1)}
        .modal-footer {display: flex;justify-content: space-between;align-items: center;margin-top: 20px;padding-top: 15px;border-top: 1px solid #ddd;}
        .jwcfd-left { margin-right: auto; }
        .jwcfd-right { display: flex; gap: 10px; }
        .jwcfd-link {text-decoration: none;padding: 8px 12px;border-radius: 5px;font-size: 14px;transition: all 0.2s;}
        .jwcfd-deactivate { color: #a00; }
        .jwcfd-deactivate:hover { color: #dc3232; background: #f8d7da; }
        .jwcfd-active { background: #3858e9; color: white; border:#3858e9; cursor: pointer;}
        .jwcfd-active:hover { background: #2a46c7;color: white !important;}
        .jwcfd-close { background: #f0f0f0; color: #2c3338; }
        .jwcfd-close:hover { background: #e0e0e0; }
        #jwcfe-loading { display:none; margin-top:1em; color:#646970; font-style:italic }
    </style>
    <div class="jwcfe-modal-header">
        <img src="{{logo_url}}" alt="Logo" style="height: 30px; vertical-align: middle;">
        <h3 class="jwcfe-modal-title" style="display: inline-block; vertical-align: middle;">Quick Feedback</h3>
    </div>
    <h3>If you have a moment, please let us know why you want to deactivate this plugin</h3>
    <form id="jwcfe-feedback-form">
        <div class="jwcfe-form-group"><label class="jwcfe-radio-label"><input type="radio" name="reason" value="Not working properly" class="jwcfe-radio-input"><span>Not working as expected</span></label></div>
        <div class="jwcfe-form-group"><label class="jwcfe-radio-label"><input type="radio" name="reason" value="Broke my website" class="jwcfe-radio-input"><span>Broke my website</span></label></div>
        <div class="jwcfe-form-group"><label class="jwcfe-radio-label"><input type="radio" name="reason" value="Found another plugin" class="jwcfe-radio-input"><span>Found another better plugin</span></label></div>
        <div class="jwcfe-form-group"><label class="jwcfe-radio-label"><input type="radio" name="reason" value="Lacking features" class="jwcfe-radio-input"><span>Missing important features</span></label></div>
        <div class="jwcfe-form-group"><label class="jwcfe-radio-label"><input type="radio" name="reason" value="Other" class="jwcfe-radio-input"><span>Other reasons</span></label></div>
        <div class="jwcfe-form-group"><textarea class="jwcfe-textarea" name="other_reason" placeholder="Please help us understand your decision better..."></textarea></div>
        <p>This form is only for getting your valuable feedback. We do not collect your personal data.<br>To know more read our <a href="https://jcodex.com/privacy-policy/">Privacy Policy.</a></p>
        <footer class="modal-footer">
            <div class="jwcfd-left"><a class="jwcfd-link jwcfd-deactivate" href="#">Skip & Deactivate</a></div>
            <div class="jwcfd-right">
                <a class="jwcfd-link jwcfd-active" target="_blank" href="https://jcodex.com/support/">Get Support</a>
                <button type="submit" class="jwcfd-link jwcfd-active jwcfd-submit-deactivate">Submit and Deactivate</button>
                <a class="jwcfd-link jwcfd-close" href="#">Cancel</a>
            </div>
        </footer>
        <p id="jwcfe-loading">Submitting your feedback...</p>
    </form>
</div>
