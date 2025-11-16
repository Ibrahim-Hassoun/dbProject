<?php
session_start();
session_destroy();
header('Location: ../login/index.html?logout=1');
exit;
