/* eslint-disable no-console */
/* eslint-disable import/no-extraneous-dependencies */
const chokidar = require('chokidar');
const fs = require('fs');
const path = require('path');
const uglify = require('uglify-js');

const watch = [
    'public/js/classes',
    'public/js/elements',
    'public/js/handlers',
];

let cwd = __dirname;
if (cwd.includes('/bin')) {
    cwd = cwd.substring(0, cwd.lastIndexOf('/bin'));
}
process.chdir(cwd);

function getFileContents(file) {
    try {
        return fs.readFileSync(file);
    } catch {
        console.error(`Could not access file: ${file}`);
    }
    return '';
}

function processDir(dir) {
    let data = '';
    fs.readdirSync(dir).forEach((item) => {
        const file = path.join(dir, item);
        if (fs.lstatSync(file).isDirectory()) {
            data += processDir(file);
        } else {
            data += getFileContents(file);
        }
    });
    return data;
}

function compileScripts() {
    let data = getFileContents('public/js/base.js');
    watch.forEach((dir) => {
        data += processDir(dir);
    });
    const minify = uglify.minify(
        {
            'main.js': data
        },
        {
            sourceMap: {
                filename: 'main.js',
                url: 'main.js.map'
            }
        }
    );
    try {
        fs.writeFileSync('public/js/main.js', minify.code);
        fs.writeFileSync('public/js/main.js.map', minify.map);
    } catch (err) {
        console.error('Could not combine scripts.', err);
    }
}

// Initialize watcher.
const watcher = chokidar.watch(watch, {
    ignoreInitial: true,
    persistent: true
});

watcher
    .on('add', compileScripts)
    .on('addDir', compileScripts)
    .on('change', compileScripts)
    .on('unlink', compileScripts);

console.log('Auto compiling scripts on save. Press Ctrl + C to stop watching for changes.');
