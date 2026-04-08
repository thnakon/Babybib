<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

jsonResponse(['success' => false, 'error' => 'Profile picture uploads are disabled'], 410);
