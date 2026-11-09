{{--
    Generic multi-file picker: lists chosen files with a remove button per file,
    so users can drop a mistaken selection without re-picking everything.
    Mark inputs with `data-file-picker` (optionally `data-max-files`), then
    include this partial inside the view's @push('scripts') block.
--}}
<script>
    (function () {
        var formatSize = function (bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        };

        document.querySelectorAll('input[type=file][data-file-picker]').forEach(function (input) {
            if (input.dataset.pickerReady || typeof DataTransfer === 'undefined') {
                input.dataset.pickerReady = 'true';
                return;
            }
            input.dataset.pickerReady = 'true';

            var maxFiles = parseInt(input.dataset.maxFiles || '10', 10);
            var selected = [];

            var panel = document.createElement('div');
            panel.className = 'mt-2 hidden overflow-hidden rounded-xl border border-base-300/80 bg-base-100';

            var heading = document.createElement('p');
            heading.className = 'border-b border-base-300/70 px-3 py-2 text-xs font-medium text-base-content/65';
            var count = document.createElement('span');
            count.className = 'text-base-content/45';
            heading.appendChild(document.createTextNode('Selected files '));
            heading.appendChild(count);

            var items = document.createElement('ul');
            items.className = 'divide-y divide-base-300/70';

            panel.append(heading, items);

            var warning = document.createElement('p');
            warning.className = 'mt-1 hidden text-xs font-medium text-warning';
            warning.textContent = 'Only the first ' + maxFiles + ' files are kept. Remove one file before adding another.';

            input.after(panel, warning);

            function syncInput() {
                var transfer = new DataTransfer();
                selected.forEach(function (file) {
                    transfer.items.add(file);
                });
                input.files = transfer.files;
            }

            function render() {
                items.innerHTML = '';
                panel.classList.toggle('hidden', selected.length === 0);
                count.textContent = selected.length ? '(' + selected.length + ' / ' + maxFiles + ')' : '';

                selected.forEach(function (file, index) {
                    var row = document.createElement('li');
                    row.className = 'flex items-center justify-between gap-3 px-3 py-2';

                    var info = document.createElement('div');
                    info.className = 'min-w-0';

                    var name = document.createElement('p');
                    name.className = 'truncate text-sm font-medium';
                    name.textContent = file.name;

                    var meta = document.createElement('p');
                    meta.className = 'text-xs text-base-content/50';
                    meta.textContent = formatSize(file.size);

                    info.append(name, meta);

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-ghost btn-xs shrink-0 text-error';
                    remove.setAttribute('aria-label', 'Remove ' + file.name);
                    remove.innerHTML = '<i class="fas fa-xmark"></i> Remove';
                    remove.addEventListener('click', function () {
                        selected.splice(index, 1);
                        syncInput();
                        render();
                        warning.classList.add('hidden');
                    });

                    row.append(info, remove);
                    items.appendChild(row);
                });
            }

            input.addEventListener('change', function () {
                var hitLimit = false;

                Array.prototype.forEach.call(input.files, function (file) {
                    if (selected.length >= maxFiles) {
                        hitLimit = true;
                        return;
                    }

                    var duplicate = selected.some(function (existing) {
                        return existing.name === file.name
                            && existing.size === file.size
                            && existing.lastModified === file.lastModified;
                    });

                    if (!duplicate) selected.push(file);
                });

                syncInput();
                render();
                warning.classList.toggle('hidden', !hitLimit);
            });
        });
    })();
</script>
