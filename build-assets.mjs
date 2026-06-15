import { copyFileSync, mkdirSync } from 'node:fs';
mkdirSync('public/assets', { recursive: true });
copyFileSync('assets/app.css', 'public/assets/app.css');
copyFileSync('assets/app.js', 'public/assets/app.js');
console.log('Assets built to public/assets');
