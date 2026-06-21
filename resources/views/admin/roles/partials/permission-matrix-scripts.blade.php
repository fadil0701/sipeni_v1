@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const updateSelectedCount = function () {
        const el = document.getElementById('selected-count');
        if (!el) return;
        el.textContent = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    };

    document.querySelectorAll('.parent-mod-toggle').forEach(function (parentCb) {
        parentCb.addEventListener('change', function () {
            const modKey = this.dataset.module;
            const action = this.dataset.action;
            document.querySelectorAll('.child-cb-' + modKey + '-' + action).forEach(function (child) {
                child.checked = parentCb.checked;
            });
            updateSelectedCount();
        });
    });

    document.querySelectorAll('input[name="permissions[]"]').forEach(function (child) {
        child.addEventListener('change', function () {
            const parts = this.className.match(/child-cb-(.+)-(.+)/);
            if (!parts) return;
            const modKey = parts[1];
            const action = parts[2];
            const parent = document.querySelector('.parent-mod-toggle[data-module="' + modKey + '"][data-action="' + action + '"]');
            if (!parent) return;
            const siblings = document.querySelectorAll('.child-cb-' + modKey + '-' + action);
            const total = siblings.length;
            const checked = document.querySelectorAll('.child-cb-' + modKey + '-' + action + ':checked').length;
            if (checked === 0) {
                parent.checked = false;
                parent.indeterminate = false;
            } else if (checked === total) {
                parent.checked = true;
                parent.indeterminate = false;
            } else {
                parent.checked = false;
                parent.indeterminate = true;
            }
            updateSelectedCount();
        });
    });

    document.getElementById('select-all-parents')?.addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('.parent-mod-toggle').forEach(function (cb) {
            cb.checked = checked;
            cb.indeterminate = false;
            const modKey = cb.dataset.module;
            const action = cb.dataset.action;
            document.querySelectorAll('.child-cb-' + modKey + '-' + action).forEach(function (child) {
                child.checked = checked;
            });
        });
        updateSelectedCount();
    });

    document.querySelectorAll('.parent-mod-toggle').forEach(function (parentCb) {
        const modKey = parentCb.dataset.module;
        const action = parentCb.dataset.action;
        const siblings = document.querySelectorAll('.child-cb-' + modKey + '-' + action);
        const total = siblings.length;
        const checked = document.querySelectorAll('.child-cb-' + modKey + '-' + action + ':checked').length;
        if (checked === 0) {
            parentCb.checked = false;
            parentCb.indeterminate = false;
        } else if (checked === total) {
            parentCb.checked = true;
            parentCb.indeterminate = false;
        } else {
            parentCb.checked = false;
            parentCb.indeterminate = true;
        }
    });

    updateSelectedCount();
});
</script>
@endpush
