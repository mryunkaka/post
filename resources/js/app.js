import './bootstrap';
import Quill from 'quill';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.Quill = Quill;

document.addEventListener('DOMContentLoaded', () => {
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

        quill.on('text-change', () => {
            input.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });

        const form = element.closest('form');

        form?.addEventListener('submit', () => {
            input.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });
    });
});

Alpine.start();
