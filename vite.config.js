import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/*
 * Only this application's own assets are built here.
 *
 * The third-party front end -- Foundation 5, jQuery, Modernizr, Konami-JS,
 * snowfall and the Ace editor -- stays vendored in public/ and keeps loading
 * through plain <script> and <link> tags, because the templates depend on those
 * globals being in place synchronously and on Foundation's own load order.
 * Bundling them would buy nothing and risk a great deal.
 *
 * The page-specific entries exist so that a template pulls in only what it
 * needs. Vite emits modules, which are deferred and therefore execute before
 * DOMContentLoaded -- so the inline $(document).ready() blocks in the templates
 * still find what they expect.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/print.css',
                'resources/js/app.js',
                'resources/js/problem-solver.js',
                'resources/js/vhost-create.js',
                'resources/js/page-editor.js',
            ],
            refresh: true,
        }),
    ],
});
