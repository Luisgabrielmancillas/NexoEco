@push('styles')
<style>
    .seller-page { padding: 36px 0 64px; }
    .seller-shell { display: grid; grid-template-columns: minmax(280px, .8fr) minmax(0, 1.3fr); gap: 28px; max-width: 1120px; margin: auto; align-items: start; }
    .seller-sidebar { position: sticky; top: 94px; padding: 36px; border-radius: 24px; background: #F2EADF; overflow: hidden; }
    .seller-eyebrow { display: inline-block; color: #B83E19; font-size: 11px; font-weight: 800; letter-spacing: 1.7px; text-transform: uppercase; }
    .seller-sidebar h1 { margin: 18px 0 14px; font-size: clamp(28px, 3vw, 38px); font-weight: 800; letter-spacing: -1.6px; line-height: 1.15; }
    .seller-sidebar h1 span { color: #C34820; }
    .seller-sidebar p { color: #645A51; font-size: 14px; line-height: 1.8; }
    .seller-steps { list-style: none; margin: 30px 0; padding: 0; }
    .seller-steps li { display: flex; align-items: center; gap: 13px; padding: 12px 0; color: #746C66; font-size: 14px; }
    .seller-step-number { display: grid; place-items: center; width: 34px; height: 34px; flex-shrink: 0; border-radius: 50%; border: 1px solid #CEC1B3; font-size: 12px; font-weight: 800; }
    .seller-steps [aria-current="step"] { color: #1C1917; font-weight: 800; }
    .seller-steps [aria-current="step"] .seller-step-number { background: #E85D2F; border-color: #E85D2F; color: white; }
    .seller-steps .is-complete .seller-step-number { background: #1C1917; border-color: #1C1917; color: white; }
    .seller-security { display: flex; gap: 10px; padding-top: 22px; border-top: 1px solid #D9CDC0; color: #645A51; font-size: 12px; line-height: 1.7; }
    .seller-security svg { flex-shrink: 0; width: 20px; height: 20px; color: #B83E19; }
    .seller-card { padding: 36px; border: 1px solid var(--nexo-border); border-radius: 24px; background: white; box-shadow: 0 12px 40px rgba(28,25,23,.04); }
    .seller-card h2 { margin: 10px 0; font-size: 27px; font-weight: 800; letter-spacing: -.8px; }
    .seller-description { margin: 0 0 28px; color: var(--nexo-muted); font-size: 14px; line-height: 1.8; }
    .seller-field { margin-bottom: 20px; }
    .seller-field label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; }
    .seller-field input:not([type="checkbox"]), .seller-field textarea { width: 100%; min-width: 0; padding: 12px 14px; color: #1C1917; background: #FFFEFC; border: 1px solid #DED6CC; border-radius: 11px; font-size: 14px; }
    .seller-field input:focus, .seller-field textarea:focus { outline: 2px solid #E85D2F; outline-offset: 2px; border-color: #E85D2F; box-shadow: none; }
    .seller-field textarea { min-height: 105px; resize: vertical; }
    .seller-help { display: block; margin-top: 7px; color: #746C66; font-size: 12px; line-height: 1.6; }
    .seller-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .seller-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 28px; padding-top: 22px; border-top: 1px solid #E8E0D4; }
    .seller-button { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 13px 22px; border: none; border-radius: 11px; background: #E85D2F; color: white; cursor: pointer; font-size: 14px; font-weight: 700; text-decoration: none; }
    .seller-button:hover { background: #D94F25; }
    .seller-button:disabled { opacity: .65; cursor: wait; }
    .seller-button:focus-visible, .seller-link:focus-visible { outline: 3px solid #E85D2F; outline-offset: 4px; }
    .seller-secondary { background: #FAF7F2; color: #645A51; border: 1px solid #E8E0D4; }
    .seller-secondary:hover { background: #F2EADF; }
    .seller-link { color: #B83E19; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
    .seller-footnote { margin-top: 24px; font-size: 13px; color: #746C66; line-height: 1.8; }
    .seller-notice { margin: 0 0 24px; padding: 16px 18px; border-radius: 12px; font-size: 13px; line-height: 1.7; background: #FFF0EA; color: #8E3419; }
    .seller-notice ul { padding-left: 18px; margin: 6px 0; }
    .seller-success { background: #EDF5EE; color: #35553C; }
    .seller-upload { margin-bottom: 16px; padding: 18px; border: 1px dashed #D8C6B5; border-radius: 14px; background: #FFFCF8; }
    .seller-upload input[type="file"] { padding: 10px 0; border: none; background: transparent; font-size: 12px; }
    .seller-upload input::file-selector-button { margin-right: 12px; padding: 9px 12px; border: 1px solid #E8E0D4; border-radius: 8px; background: white; color: #645A51; cursor: pointer; }
    .seller-summary { margin: 20px 0; padding: 18px; border: 1px solid #E8E0D4; border-radius: 14px; background: #FAF7F2; font-size: 13px; line-height: 1.8; overflow-wrap: anywhere; }
    .seller-summary h3 { margin: 0 0 8px; font-size: 14px; font-weight: 800; }
    .seller-status-icon { display: grid; place-items: center; width: 62px; height: 62px; margin-bottom: 22px; border-radius: 18px; background: #FFF0EA; color: #C34820; }
    .seller-status-icon svg { width: 30px; height: 30px; }
    .seller-email { color: #1C1917; font-weight: 700; overflow-wrap: anywhere; }
    .verification-code-form { margin-top: 24px; }
    .verification-code-form input#codigo { width: 230px; max-width: 100%; padding: 16px 10px 16px 24px; font-size: 30px; font-weight: 800; text-align: center; letter-spacing: 14px; font-variant-numeric: tabular-nums; }
    .seller-checklist { margin: 24px 0; padding: 0; list-style: none; }
    .seller-checklist li { padding: 12px 0; border-bottom: 1px solid #F0EAE2; font-size: 13px; color: #645A51; }
    .seller-checklist li::before { content: '✓'; color: #47714C; font-weight: 800; margin-right: 12px; }
    .seller-password { position: relative; }
    .seller-password input { padding-right: 65px !important; }
    .seller-password button { position: absolute; top: 12px; right: 12px; padding: 2px; border: 0; background: transparent; color: #B83E19; font-size: 12px; font-weight: 700; cursor: pointer; }
    .seller-page [hidden] { display: none !important; }
    .seller-progress { display: flex; justify-content: space-between; margin-bottom: 20px; color: #746C66; font-size: 12px; }
    .seller-progress progress { width: 100px; height: 6px; align-self: center; accent-color: #E85D2F; }
    .seller-progress progress { appearance: none; border: 0; border-radius: 6px; overflow: hidden; background: #EEE4DB; }
    .seller-progress progress::-webkit-progress-bar { background: #EEE4DB; }
    .seller-progress progress::-webkit-progress-value { background: #E85D2F; }
    .seller-progress progress::-moz-progress-bar { background: #E85D2F; }
    [data-wizard-next], [data-wizard-submit] { margin-left: auto; }
    @media (max-width: 800px) { .seller-page { padding: 20px 0 40px; } .seller-shell { grid-template-columns: 1fr; gap: 18px; } .seller-sidebar { position: static; padding: 24px; } .seller-sidebar h1 { font-size: 29px; } .seller-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 20px 0; } .seller-steps li { flex-direction: column; align-items: flex-start; font-size: 11px; gap: 8px; } .seller-card { padding: 24px; } }
    @media (max-width: 420px) { .seller-fields-grid { grid-template-columns: 1fr; gap: 0; } .seller-actions { flex-wrap: wrap; } .seller-button { padding: 12px 16px; } .seller-card { padding: 22px 18px; } .seller-steps { gap: 6px; } }
</style>
@endpush
