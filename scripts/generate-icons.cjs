/**
 * Menghasilkan app/Support/Icons.php dari paket npm `heroicons`
 * (outline 24px, MIT License). Jalankan: node scripts/generate-icons.cjs
 */
const fs = require('fs');

const names = [
    'academic-cap',
    'adjustments-horizontal',
    'arrow-down-tray',
    'arrow-left',
    'arrow-path',
    'arrow-right-on-rectangle',
    'arrow-trending-up',
    'bars-3',
    'bell',
    'bolt',
    'book-open',
    'briefcase',
    'building-library',
    'calendar',
    'calendar-days',
    'chart-bar',
    'chart-pie',
    'check',
    'check-circle',
    'chevron-down',
    'chevron-left',
    'chevron-right',
    'clipboard-document-check',
    'clipboard-document-list',
    'clock',
    'device-phone-mobile',
    'document-check',
    'document-text',
    'envelope',
    'exclamation-circle',
    'exclamation-triangle',
    'eye',
    'eye-slash',
    'funnel',
    'globe-alt',
    'heart',
    'home',
    'identification',
    'information-circle',
    'lock-closed',
    'magnifying-glass',
    'moon',
    'pencil-square',
    'plus',
    'presentation-chart-line',
    'question-mark-circle',
    'shield-check',
    'sparkles',
    'squares-2x2',
    'star',
    'sun',
    'table-cells',
    'trash',
    'user',
    'user-circle',
    'user-group',
    'users',
    'x-circle',
    'x-mark',
];

let map = '';

for (const name of names) {
    const svg = fs.readFileSync(`node_modules/heroicons/24/outline/${name}.svg`, 'utf8');
    const inner = svg
        .replace(/^[\s\S]*?<svg[^>]*>/, '')
        .replace(/<\/svg>[\s\S]*$/, '')
        .trim()
        .replace(/\r?\n\s*/g, ' ');

    if (inner.includes("'")) {
        throw new Error(`Single quote found in ${name}`);
    }

    map += `        '${name}' => '${inner}',\n`;
}

const php = `<?php

namespace App\\Support;

/**
 * Path SVG Heroicons (outline 24px, MIT License) yang dipakai aplikasi.
 * Digenerate dari paket npm \`heroicons\` - jalankan scripts/generate-icons.cjs.
 */
class Icons
{
    public static function get(string $name): string
    {
        return self::MAP[$name] ?? self::MAP['question-mark-circle'];
    }

    private const MAP = [
${map}    ];
}
`;

fs.mkdirSync('app/Support', { recursive: true });
fs.writeFileSync('app/Support/Icons.php', php);

console.log(`Generated app/Support/Icons.php with ${names.length} icons.`);
