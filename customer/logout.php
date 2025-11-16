<?php
session_start();
session_destroy();
header('Location: ../register/index.html');
exit;
