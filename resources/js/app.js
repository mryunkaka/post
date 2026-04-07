import './bootstrap';
import Quill from 'quill';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.Quill = Quill;

document.addEventListener('DOMContentLoaded', () => {
    const quillRegistry = new Map();

    document.querySelectorAll('form[data-local-draft]').forEach((form) => {
        const storageKey = `todaksiring:${form.getAttribute('data-local-draft')}`;
        const draftFields = Array.from(
            form.querySelectorAll('input[name], textarea[name], select[name]')
        ).filter((field) => field.type !== 'file');

        const restoreFieldValue = (field, value) => {
            if (value === undefined || value === null) {
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
                return;
            }

            if (field.type === 'radio') {
                field.checked = field.value === value;
                return;
            }

            field.value = value;
        };

        const syncDraftToStorage = () => {
            const payload = {};

            draftFields.forEach((field) => {
                if (! field.name) {
                    return;
                }

                if (field.type === 'checkbox') {
                    payload[field.name] = field.checked;
                    return;
                }

                if (field.type === 'radio') {
                    if (field.checked) {
                        payload[field.name] = field.value;
                    }

                    return;
                }

                payload[field.name] = field.value;
            });

            window.localStorage.setItem(storageKey, JSON.stringify(payload));
        };

        try {
            const restoredPayload = JSON.parse(window.localStorage.getItem(storageKey) ?? '{}');

            draftFields.forEach((field) => {
                restoreFieldValue(field, restoredPayload[field.name]);
            });
        } catch {
            window.localStorage.removeItem(storageKey);
        }

        draftFields.forEach((field) => {
            const eventName = field.tagName === 'SELECT' || field.type === 'checkbox' || field.type === 'radio'
                ? 'change'
                : 'input';

            field.addEventListener(eventName, syncDraftToStorage);
        });

        form.addEventListener('submit', () => {
            window.localStorage.removeItem(storageKey);
        });
    });

    document.querySelectorAll('[data-rich-editor]').forEach((element) => {
        const inputId = element.getAttribute('data-input');
        const input = inputId ? document.getElementById(inputId) : null;

        if (! input) {
            return;
        }

        const quill = new Quill(element, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ header: [2, 3, false] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link'],
                    ['clean'],
                ],
            },
        });

        const initialHtml = input.value?.trim();

        if (initialHtml) {
            quill.clipboard.dangerouslyPasteHTML(initialHtml);
        }

        if (input.name) {
            quillRegistry.set(input.name, quill);
        }

        quill.on('text-change', () => {
            input.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        const form = element.closest('form');

        form?.addEventListener('submit', () => {
            input.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });
    });

    document.querySelectorAll('[data-clear-local-draft]').forEach((button) => {
        button.addEventListener('click', () => {
            const draftKey = button.getAttribute('data-clear-local-draft');

            if (! draftKey) {
                return;
            }

            const form = document.querySelector(`form[data-local-draft="${draftKey}"]`);

            if (! form) {
                return;
            }

            window.localStorage.removeItem(`todaksiring:${draftKey}`);
            form.reset();

            form.querySelectorAll('input[name], textarea[name], select[name]').forEach((field) => {
                if (field.type === 'hidden') {
                    return;
                }

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = field.defaultChecked;
                    return;
                }

                field.value = field.defaultValue ?? '';
            });

            form.querySelectorAll('[data-rich-editor]').forEach((element) => {
                const inputId = element.getAttribute('data-input');
                const input = inputId ? document.getElementById(inputId) : null;

                if (! input || ! input.name) {
                    return;
                }

                const quill = quillRegistry.get(input.name);

                input.value = input.defaultValue ?? '';

                if (quill) {
                    quill.setContents([]);

                    if (input.value.trim() !== '') {
                        quill.clipboard.dangerouslyPasteHTML(input.value);
                    }
                }
            });
        });
    });
});

Alpine.start();
