<?php

function convertHexField($field) {
  if($field === NULL) {
    return NULL;
  } else {
    return hex2bin($field);
  }
}