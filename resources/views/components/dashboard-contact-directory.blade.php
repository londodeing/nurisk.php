<div class="offcanvas offcanvas-end" tabindex="-1" id="contactDirectory" aria-labelledby="contactDirectoryLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="contactDirectoryLabel">
            <i class="bi bi-telephone me-2"></i>Kontak Operasional
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 border-bottom">
            <input type="text" class="form-control form-control-sm" id="cc-contact-search" placeholder="Cari kontak...">
        </div>
        <div class="list-group list-group-flush cc-contact-list" data-cc-widget="contact-directory" data-cc-interval="60">
            @foreach($contacts ?? [] as $contact)
            <div class="list-group-item list-group-item-action cc-contact-item" data-search="{{ strtolower($contact['nama'] ?? '') }} {{ strtolower($contact['unit'] ?? '') }}">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-{{ $contact['color'] ?? 'secondary' }} rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-semibold small">{{ $contact['nama'] ?? '—' }}</div>
                        <div class="text-muted small">{{ $contact['jabatan'] ?? '' }}</div>
                        <div class="text-muted small">{{ $contact['unit'] ?? '' }}</div>
                        <div class="mt-1">
                            @if(!empty($contact['no_hp']))
                                @php $phone = '62' . ltrim($contact['no_hp'], '0'); @endphp
                                <a href="https://wa.me/{{ $phone }}" target="_blank" class="btn btn-success btn-sm me-1">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="tel:{{ $contact['no_hp'] }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
