@php
    $name = $name ?? 'square-3-stack-3d';
    $classes = $classes ?? 'h-4 w-4';
@endphp

@switch($name)
    @case('chevron-left')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        @break
    @case('chevron-right')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
        </svg>
        @break
    @case('eye')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178a1.012 1.012 0 0 1 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        @break
    @case('pencil-square')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931ZM19.5 7.125 16.875 4.5M18 14.25v4.125A2.625 2.625 0 0 1 15.375 21H5.625A2.625 2.625 0 0 1 3 18.375V8.625A2.625 2.625 0 0 1 5.625 6H9.75" />
        </svg>
        @break
    @case('trash')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084A2.25 2.25 0 0 1 5.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.875c0-1.121-.84-2.067-1.955-2.182A51.964 51.964 0 0 0 12 2.25c-1.845 0-3.645.114-5.378.331C5.507 2.708 4.667 3.654 4.667 4.875V5.25m14.25 0H4.667" />
        </svg>
        @break
    @case('check')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        @break
    @case('x-mark')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        @break
    @case('flag')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18m0-12c1.25 0 2.5-.5 3.75-1.5C8 6.5 9.25 6 10.5 6c1.25 0 2.5.5 3.75 1.5C15.5 8.5 16.75 9 18 9s2.5-.5 3.75-1.5V3.75C20.5 4.25 19.25 4.5 18 4.5c-1.25 0-2.5-.5-3.75-1.5C13 2 11.75 1.5 10.5 1.5 9.25 1.5 8 2 6.75 3 5.5 4 4.25 4.5 3 4.5" />
        </svg>
        @break
    @case('arrow-path')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356m-4.992 4.992A9 9 0 0 0 5.64 5.64m0 0V1.5m0 4.14h4.14m0 0A9 9 0 0 1 20.364 18.36m0 0v4.14m0-4.14h-4.14" />
        </svg>
        @break
    @case('sparkles')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18l-.813-2.096a4.5 4.5 0 0 0-2.592-2.592L3.5 12l2.095-.813a4.5 4.5 0 0 0 2.592-2.592L9 6.5l.813 2.095a4.5 4.5 0 0 0 2.592 2.592L14.5 12l-2.095.813a4.5 4.5 0 0 0-2.592 2.592ZM18.259 8.715 18 9.5l-.259-.785a2.25 2.25 0 0 0-1.456-1.456L15.5 7l.785-.259a2.25 2.25 0 0 0 1.456-1.456L18 4.5l.259.785a2.25 2.25 0 0 0 1.456 1.456L20.5 7l-.785.259a2.25 2.25 0 0 0-1.456 1.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
        @break
    @case('document-text')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.625a1.125 1.125 0 0 0-1.125-1.125H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h10.875A1.875 1.875 0 0 0 19.5 17.625V16.5a1.875 1.875 0 0 0-1.875-1.875H15a.75.75 0 0 1-.75-.75v-1.5a.75.75 0 0 1 .75-.75h4.5Zm-12 3h7.5m-7.5-3h4.5m-4.5-3h7.5" />
        </svg>
        @break
    @default
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
@endswitch
