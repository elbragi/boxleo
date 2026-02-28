<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset successful";
} else {
    echo "OPcache NOT enabled";
}
unlink(__FILE__);
