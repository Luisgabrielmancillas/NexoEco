@push('styles')
<style>
    .seller-page { padding: 36px 0 64px; }
    .seller-shell { display: grid; grid-template-columns: minmax(280px, .8fr) minmax(0, 1.3fr); gap: 28px; max-width: 1120px; margin: auto; align-items: start; }
    .seller-sidebar { position: sticky; top: 94px; padding: 36px; border-radius: 24px; background: var(--color-primary-soft); overflow: hidden; }
    .seller-eyebrow { display: inline-block; color: var(--color-primary-text); font-size: 11px; font-weight: 800; letter-spacing: 1.7px; text-transform: uppercase; }
    .seller-sidebar h1 { margin: 18px 0 14px; font-size: clamp(28px, 3vw, 38px); font-weight: 800; letter-spacing: -1.6px; line-height: 1.15; }
    .seller-sidebar h1 span { color: var(--color-primary-text); }
    .seller-sidebar p { color: var(--color-text-secondary); font-size: 14px; line-height: 1.8; }
    .seller-steps { list-style: none; margin: 30px 0; padding: 0; }
    .seller-steps li { display: flex; align-items: center; gap: 13px; padding: 12px 0; color: var(--color-text-secondary); font-size: 14px; }
    .seller-step-number { display: grid; place-items: center; width: 34px; height: 34px; flex-shrink: 0; border-radius: 50%; border: 1px solid var(--color-border); font-size: 12px; font-weight: 800; }
    .seller-steps [aria-current="step"] { color: var(--color-text-primary); font-weight: 800; }
    .seller-steps [aria-current="step"] .seller-step-number { background: var(--color-primary); border-color: var(--color-primary); color: var(--color-on-primary); }
    .seller-steps .is-complete .seller-step-number { background: var(--color-dark-surface); border-color: var(--color-border); color: var(--color-on-primary); }
    .seller-security { display: flex; gap: 10px; padding-top: 22px; border-top: 1px solid var(--color-border); color: var(--color-text-secondary); font-size: 12px; line-height: 1.7; }
    .seller-security svg { flex-shrink: 0; width: 20px; height: 20px; color: var(--color-primary-text); }
    .seller-card { padding: 36px; border: 1px solid var(--nexo-border); border-radius: 24px; background: var(--color-surface); box-shadow: 0 12px 40px rgb(var(--color-text-primary-rgb) / 0.04); }
    .seller-card h2 { margin: 10px 0; font-size: 27px; font-weight: 800; letter-spacing: -.8px; }
    .seller-description { margin: 0 0 28px; color: var(--nexo-muted); font-size: 14px; line-height: 1.8; }
    .seller-field { margin-bottom: 20px; }
    .seller-field label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; }
    .seller-field input:not([type="checkbox"]), .seller-field textarea { width: 100%; min-width: 0; padding: 12px 14px; color: var(--color-text-primary); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 11px; font-size: 14px; }
    .seller-field input:focus, .seller-field textarea:focus { outline: 2px solid var(--color-primary); outline-offset: 2px; border-color: var(--color-primary); box-shadow: none; }
    .seller-field textarea { min-height: 105px; resize: vertical; }
    .seller-help { display: block; margin-top: 7px; color: var(--color-text-secondary); font-size: 12px; line-height: 1.6; }
    .seller-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .seller-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--color-border); }
    .seller-button { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 13px 22px; border: none; border-radius: 11px; background: var(--color-primary); color: var(--color-on-primary); cursor: pointer; font-size: 14px; font-weight: 700; text-decoration: none; }
    .seller-button:hover { background: var(--color-primary-hover); }
    .seller-button:disabled { opacity: .65; cursor: wait; }
    .seller-button:focus-visible, .seller-link:focus-visible { outline: 3px solid var(--color-primary); outline-offset: 4px; }
    .seller-secondary { background: var(--color-primary-soft); color: var(--color-text-secondary); border: 1px solid var(--color-border); }
    .seller-secondary:hover { background: var(--color-primary-soft); }
    .seller-link { color: var(--color-primary-text); font-weight: 700; text-decoration: none; }
    .seller-footnote { margin-top: 24px; font-size: 13px; color: var(--color-text-secondary); line-height: 1.8; }
    .seller-notice { margin: 0 0 24px; padding: 16px 18px; border-radius: 12px; font-size: 13px; line-height: 1.7; background: var(--color-primary-soft); color: var(--color-primary-text); }
    .seller-notice ul { padding-left: 18px; margin: 6px 0; }
    .seller-success { background: var(--color-success-soft); color: var(--color-success-text); }
    .seller-upload { margin-bottom: 16px; padding: 18px; border: 1px dashed var(--color-border); border-radius: 14px; background: var(--color-surface); }
    .seller-upload input[type="file"] { padding: 10px 0; border: none; background: transparent; font-size: 12px; }
    .seller-upload input::file-selector-button { margin-right: 12px; padding: 9px 12px; border: 1px solid var(--color-border-strong); border-radius: 8px; background: var(--color-surface); color: var(--color-text-secondary); cursor: pointer; }
    .seller-summary { margin: 20px 0; padding: 18px; border: 1px solid var(--color-border); border-radius: 14px; background: var(--color-primary-soft); font-size: 13px; line-height: 1.8; overflow-wrap: anywhere; }
    .seller-summary h3 { margin: 0 0 8px; font-size: 14px; font-weight: 800; }
    .seller-status-icon { display: grid; place-items: center; width: 62px; height: 62px; margin-bottom: 22px; border-radius: 18px; background: var(--color-primary-soft); color: var(--color-primary-text); }
    .seller-status-icon svg { width: 30px; height: 30px; }
    .seller-email { color: var(--color-text-primary); font-weight: 700; overflow-wrap: anywhere; }
    .verification-code-form { margin-top: 24px; }
    .verification-code-form input#codigo { width: 230px; max-width: 100%; padding: 16px 10px 16px 24px; font-size: 30px; font-weight: 800; text-align: center; letter-spacing: 14px; font-variant-numeric: tabular-nums; }
    .seller-checklist { margin: 24px 0; padding: 0; list-style: none; }
    .seller-checklist li { padding: 12px 0; border-bottom: 1px solid var(--color-border); font-size: 13px; color: var(--color-text-secondary); }
    .seller-checklist li::before { content: '✓'; color: var(--color-success-text); font-weight: 800; margin-right: 12px; }
    .seller-password { position: relative; }
    .seller-password input { padding-right: 65px !important; }
    .seller-password button { position: absolute; top: 12px; right: 12px; padding: 2px; border: 0; background: transparent; color: var(--color-primary-text); font-size: 12px; font-weight: 700; cursor: pointer; }
    .seller-page [hidden] { display: none !important; }
    .seller-progress { display: flex; justify-content: space-between; margin-bottom: 20px; color: var(--color-text-secondary); font-size: 12px; }
    .seller-progress progress { width: 100px; height: 6px; align-self: center; accent-color: var(--color-primary); }
    .seller-progress progress { appearance: none; border: 0; border-radius: 6px; overflow: hidden; background: var(--color-background-secondary); }
    .seller-progress progress::-webkit-progress-bar { background: var(--color-background-secondary); }
    .seller-progress progress::-webkit-progress-value { background: var(--color-primary); }
    .seller-progress progress::-moz-progress-bar { background: var(--color-primary); }
    [data-wizard-next], [data-wizard-submit] { margin-left: auto; }
    @media (max-width: 800px) { .seller-page { padding: 20px 0 40px; } .seller-shell { grid-template-columns: 1fr; gap: 18px; } .seller-sidebar { position: static; padding: 24px; } .seller-sidebar h1 { font-size: 29px; } .seller-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 20px 0; } .seller-steps li { flex-direction: column; align-items: flex-start; font-size: 11px; gap: 8px; } .seller-card { padding: 24px; } }
    @media (max-width: 420px) { .seller-fields-grid { grid-template-columns: 1fr; gap: 0; } .seller-actions { flex-wrap: wrap; } .seller-button { padding: 12px 16px; } .seller-card { padding: 22px 18px; } .seller-steps { gap: 6px; } }
</style>
@endpush
