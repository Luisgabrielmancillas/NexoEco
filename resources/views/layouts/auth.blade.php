@extends('layouts.base')
@section('body-class', 'auth-page')
@section('site-header')
<div class="auth-background" aria-hidden="true">
    <span class="auth-bubble auth-bubble-one"></span>
    <span class="auth-bubble auth-bubble-two"></span>
    <span class="auth-bubble auth-bubble-three"></span>
</div>
<header class="auth-header nexo-container"><a class="market-brand" href="{{ url('/') }}"><span class="market-brand-dot"></span>NexoEco</a><a class="skip-link" href="#main-content">Saltar al contenido</a></header>
@endsection
@push('styles')
<style>
    .auth-page {
        isolation: isolate;
        background:
            radial-gradient(circle at 10% 20%, rgb(var(--color-primary-rgb) / .22), transparent 28%),
            radial-gradient(circle at 90% 80%, rgb(var(--color-primary-rgb) / .1), transparent 30%),
            linear-gradient(135deg, var(--color-surface), var(--color-surface));
    }
    .auth-background { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: -1; }
    .auth-bubble { position: absolute; border-radius: 50%; animation: auth-float 6s ease-in-out infinite; }
    .auth-bubble-one { width: 240px; height: 240px; top: -80px; right: 34%; background: rgb(var(--color-primary-rgb) / .14); }
    .auth-bubble-two { width: 150px; height: 150px; bottom: 60px; left: 42%; background: rgb(var(--color-primary-rgb) / .1); animation-delay: 1s; }
    .auth-bubble-three { width: 90px; height: 90px; top: 110px; right: 70px; background: rgb(var(--color-primary-rgb) / .18); animation-delay: 2s; }
    .auth-page .register-card, .auth-page .seller-card { backdrop-filter: blur(18px); }
    @keyframes auth-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    @media (prefers-reduced-motion: reduce) { .auth-bubble { animation: none; } }
    .auth-header { padding-block: 24px; }
    .auth-header .market-brand { width: fit-content; font-size: 30px; }
    .auth-page .register-page, .auth-page .seller-page { min-height: auto; padding-top: 20px; }
    .auth-page .register-container { max-width: 1200px; }
    .auth-page .register-intro { background: transparent; color: var(--color-text-primary); box-shadow: none; }
    .auth-page .register-intro-title { font-size: clamp(36px, 5vw, 64px); }
    .auth-page .register-intro-text, .auth-page .register-seller-cta p { color: var(--color-text-secondary); font-size: 16px; }
    .auth-page .register-badge { color: var(--color-primary); background: var(--color-surface); }
    .auth-page .register-benefit, .auth-page .register-seller-cta { background: rgb(var(--color-surface-rgb) / .8); border-color: var(--color-border); }
    .auth-page .register-card, .auth-page .seller-card { background: rgb(var(--color-surface-rgb) / .9); border-radius: 34px; box-shadow: 0 24px 60px rgb(var(--color-text-primary-rgb) / .12); }
    .auth-page .register-submit { border-radius: 999px; }
    @media (min-width: 981px) { .auth-page .register-layout { grid-template-columns: 1.15fr .85fr; align-items: center; gap: 48px; } }
    /* Two distinct cards give each account choice space without a tall page. */
    .auth-register-page .auth-header { padding-block: 16px; }
    .auth-register-page .auth-header .market-brand { font-size: 28px; }
    .auth-register-page .register-page { padding: 8px 0 24px; }
    .auth-register-page .register-layout { align-items: stretch; gap: 24px; }
    .auth-register-page .register-intro { min-height: 0; padding: 30px; align-items: center; border-radius: 30px; color: var(--color-on-primary); background: linear-gradient(145deg, var(--color-primary), var(--color-primary-hover)); box-shadow: 0 18px 40px rgb(var(--color-primary-rgb) / .18); }
    .auth-register-page .register-intro::before { width: 260px; height: 260px; right: -100px; top: -100px; border: 36px solid rgb(var(--color-surface-rgb) / .09); background: transparent; }
    .auth-register-page .register-intro::after { width: 180px; height: 180px; left: -90px; bottom: -60px; background: rgb(var(--color-surface-rgb) / .08); }
    .auth-register-page .register-badge { padding: 8px 12px; color: var(--color-on-primary); background: rgb(var(--color-surface-rgb) / .12); border-color: rgb(var(--color-surface-rgb) / .25); }
    .auth-register-page .register-intro-title { font-size: clamp(32px, 3.4vw, 46px); margin-top: 18px; letter-spacing: -1.4px; line-height: 1.08; }
    .auth-register-page .register-intro-text { margin-top: 14px; font-size: 14px; line-height: 1.65; color: var(--color-on-primary); }
    .auth-register-page .register-seller-cta { margin-top: 24px; padding: 22px; border-radius: 20px; background: var(--color-surface); color: var(--color-text-primary); border-color: transparent; box-shadow: 0 10px 24px rgb(var(--color-text-primary-rgb) / .12); }
    .auth-register-page .register-seller-eyebrow { color: var(--color-primary-text); }
    .auth-register-page .register-seller-cta h2 { margin: 8px 0; font-size: 25px; letter-spacing: -.6px; }
    .auth-register-page .register-seller-cta p { margin-bottom: 16px; font-size: 13px; line-height: 1.6; }
    .auth-register-page .register-seller-cta a { padding: 13px 15px; border: 1px solid var(--color-primary-border); background: var(--color-primary-soft); color: var(--color-primary-text); border-radius: 12px; }
    .auth-register-page .register-seller-cta a:hover { background: var(--color-primary); color: var(--color-on-primary); }
    .auth-register-page .register-seller-cta a:focus-visible { outline-color: var(--color-primary); }
    .auth-register-page .register-card { padding: 30px; border-radius: 30px; border-top: 4px solid var(--color-primary); }
    .auth-register-page .register-card-title { font-size: 28px; letter-spacing: -.8px; }
    .auth-register-page .register-card-subtitle { font-size: 14px; margin-top: 8px; }
    .auth-register-page .register-card-header { margin-bottom: 20px; }
    .auth-register-page .form-group { margin-top: 16px; }
    .auth-register-page .form-label { font-size: 13px; margin-bottom: 8px; }
    .auth-register-page .form-input { min-height: 48px; border-radius: 12px; background: var(--color-background); font-size: 14px; }
    .auth-register-page .form-hint { margin-top: 7px; font-size: 11px; line-height: 1.5; }
    .auth-register-page .register-submit { min-height: 48px; margin-top: 22px; font-size: 14px; }
    .auth-register-page .register-login { margin-top: 18px; font-size: 13px; }
    .auth-register-page .register-terms { margin-top: 12px; font-size: 10px; }
    @media (min-width: 760px) {
        .auth-register-page .register-layout { grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); }
        .auth-register-page .register-card form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 18px; align-items: start; }
        .auth-register-page .form-group:nth-of-type(-n+2) { margin-top: 0; }
        .auth-register-page .register-submit, .auth-register-page .register-login, .auth-register-page .register-terms { grid-column: 1 / -1; }
    }
    @media (max-width: 759px) {
        .auth-register-page .register-layout { grid-template-columns: minmax(0, 1fr); gap: 18px; }
        .auth-register-page .register-intro { padding: 22px; border-radius: 24px; }
        .auth-register-page .register-badge { display: none; }
        .auth-register-page .register-intro-title { font-size: 30px; margin-top: 0; }
        .auth-register-page .register-intro-text { margin-top: 8px; font-size: 13px; }
        .auth-register-page .register-seller-cta { margin-top: 16px; padding: 18px; }
        .auth-register-page .register-seller-cta h2 { font-size: 22px; }
        .auth-register-page .register-card { padding: 22px; border-radius: 24px; }
    }
</style>
@endpush
