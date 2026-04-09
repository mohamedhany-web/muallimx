/**
 * اختياري على جهاز التطوير فقط — انسخ الحزم إلى public/.
 * النشر: ارفع public/vendor/excalidraw بالكامل عبر Git أو FTP؛ السيرفر لا يحتاج Node.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const destDir = path.join(root, 'public', 'vendor', 'excalidraw', 'dist');
const excPkg = path.join(root, 'node_modules', '@excalidraw', 'excalidraw', 'dist');

function cp(src, dst) {
    fs.mkdirSync(path.dirname(dst), { recursive: true });
    fs.copyFileSync(src, dst);
}

function cpDir(src, dst) {
    fs.mkdirSync(dst, { recursive: true });
    for (const name of fs.readdirSync(src)) {
        const s = path.join(src, name);
        const d = path.join(dst, name);
        if (fs.statSync(s).isDirectory()) cpDir(s, d);
        else fs.copyFileSync(s, d);
    }
}

cp(path.join(root, 'node_modules', 'react', 'umd', 'react.production.min.js'), path.join(root, 'public', 'vendor', 'excalidraw', 'react.production.min.js'));
cp(path.join(root, 'node_modules', 'react-dom', 'umd', 'react-dom.production.min.js'), path.join(root, 'public', 'vendor', 'excalidraw', 'react-dom.production.min.js'));
cp(path.join(excPkg, 'excalidraw.production.min.js'), path.join(destDir, 'excalidraw.production.min.js'));
fs.rmSync(path.join(destDir, 'excalidraw-assets'), { recursive: true, force: true });
cpDir(path.join(excPkg, 'excalidraw-assets'), path.join(destDir, 'excalidraw-assets'));
console.log('Synced Excalidraw → public/vendor/excalidraw');
