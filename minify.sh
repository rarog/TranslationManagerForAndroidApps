#!/bin/bash
yui-compressor --type css -o ./public/css/style.min.css ./public/css/style.css
yui-compressor --type js -o ./public/js/fixJumpingNavbar.min.js ./public/js/fixJumpingNavbar.js
yui-compressor --type js -o ./public/js/translations.min.js ./public/js/translations.js

