{{-- Modal konfirmasi global (dipakai oleh app-layout.js / window.Sipeni.confirm) --}}
<div
    id="global-confirm-modal"
    class="sipeni-confirm-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="global-confirm-title"
    aria-hidden="true"
>
    <div data-confirm-backdrop class="sipeni-confirm-modal__backdrop" aria-hidden="true"></div>
    <div class="sipeni-confirm-modal__dialog" data-confirm-dialog>
        <div class="sipeni-confirm-modal__body">
            <div class="sipeni-confirm-modal__icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"></path>
                </svg>
            </div>
            <div class="sipeni-confirm-modal__content">
                <h3 id="global-confirm-title" class="sipeni-confirm-modal__title">Konfirmasi Aksi</h3>
                <p data-confirm-message class="sipeni-confirm-modal__message">Apakah Anda yakin ingin melanjutkan?</p>
            </div>
        </div>
        <div class="sipeni-confirm-modal__actions" data-confirm-actions>
            <button type="button" data-confirm-cancel class="btn-secondary-ui sipeni-confirm-modal__btn">Batal</button>
            <button type="button" data-confirm-ok class="btn-primary-ui sipeni-confirm-modal__btn">Ya</button>
        </div>
    </div>
</div>
