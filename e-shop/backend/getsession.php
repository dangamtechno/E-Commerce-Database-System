<?php
require_once 'session.php';

if (sessionExists()) {
  echo 'true';
} else {
  echo 'false';
}
?>
