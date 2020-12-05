<?php

namespace Tests;

use Brain\Monkey\Functions;

// Mock the template dir location.
Functions\when('get_template_directory')->justReturn(dirname(__FILE__, 2) . '/data');

// Mock escaping function.
Functions\when('wp_kses_post')->returnArg();
