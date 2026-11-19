import { query } from '../lib/dom';
import { PORTAL_LOGIN_MESSAGES, type PortalLoginPayload } from '../types/contracts';

/**
 * Form login portal (PRD 4.3):
 * - format email divalidasi sebelum dikirim; validasi server tetap berlaku,
 * - tombol Login menampilkan loading & dinonaktifkan agar tidak terkirim berulang,
 * - password tersembunyi default dengan tombol tampil/sembunyikan.
 */

type FieldName = keyof PortalLoginPayload;

/** Pola sederhana untuk umpan balik cepat; keputusan akhir tetap di server (rule `email`). */
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validate(payload: PortalLoginPayload): Partial<Record<FieldName, string>> {
    const errors: Partial<Record<FieldName, string>> = {};

    if (payload.email.trim() === '') errors.email = PORTAL_LOGIN_MESSAGES['email.required'];
    else if (!EMAIL_PATTERN.test(payload.email.trim())) errors.email = PORTAL_LOGIN_MESSAGES['email.email'];

    if (payload.password === '') errors.password = PORTAL_LOGIN_MESSAGES['password.required'];

    return errors;
}

function setFieldError(form: HTMLFormElement, name: FieldName, message: string | undefined): void {
    const field = query(form, `[data-field="${name}"]`, HTMLElement);
    const input = query(form, `[name="${name}"]`, HTMLInputElement);
    const error = field ? query(field, '[data-field-error]', HTMLElement) : null;

    if (!input || !error) return;

    error.textContent = message ?? '';
    error.hidden = message === undefined;
    input.toggleAttribute('aria-invalid', message !== undefined);
    input.classList.toggle('border-danger', message !== undefined);
    input.classList.toggle('border-line-strong', message === undefined);
}

function initPasswordToggle(container: HTMLElement): void {
    const input = query(container, 'input', HTMLInputElement);
    const toggle = query(container, '[data-password-toggle]', HTMLButtonElement);
    const showIcon = query(container, '[data-icon-show]', SVGElement);
    const hideIcon = query(container, '[data-icon-hide]', SVGElement);

    if (!input || !toggle || !showIcon || !hideIcon) return;

    toggle.addEventListener('click', () => {
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(reveal));
        toggle.setAttribute('aria-label', reveal ? 'Sembunyikan password' : 'Tampilkan password');
        showIcon.toggleAttribute('hidden', reveal);
        hideIcon.toggleAttribute('hidden', !reveal);
    });
}

function setSubmitting(button: HTMLButtonElement, submitting: boolean): void {
    button.disabled = submitting;
    button.toggleAttribute('aria-busy', submitting);
    query(button, '[data-loading-icon]', SVGElement)?.toggleAttribute('hidden', !submitting);
    const label = query(button, '[data-submit-label]', HTMLElement);
    if (label) label.textContent = submitting ? 'Memproses…' : 'Login';
}

function bind(form: HTMLFormElement): void {
    const email = query(form, '[name="email"]', HTMLInputElement);
    const password = query(form, '[name="password"]', HTMLInputElement);
    const submit = query(form, '[data-login-submit]', HTMLButtonElement);

    if (!email || !password || !submit) return;

    const passwordContainer = query(form, '[data-password-input]', HTMLElement);
    if (passwordContainer) initPasswordToggle(passwordContainer);

    form.addEventListener('submit', (event) => {
        if (submit.disabled) {
            event.preventDefault();
            return;
        }

        const payload: PortalLoginPayload = { email: email.value, password: password.value };
        const errors = validate(payload);

        setFieldError(form, 'email', errors.email);
        setFieldError(form, 'password', errors.password);

        if (errors.email || errors.password) {
            event.preventDefault();
            (errors.email ? email : password).focus();
            return;
        }

        setSubmitting(submit, true);
    });

    // Kembali lewat tombol Back (bfcache) tidak boleh meninggalkan tombol dalam state loading.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) setSubmitting(submit, false);
    });
}

export function initLoginForms(): void {
    const form = query(document, 'form[data-login-form]', HTMLFormElement);
    if (form) bind(form);
}
