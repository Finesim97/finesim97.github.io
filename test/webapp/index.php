<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <title>Hello from <?php echo gethostname()?></title>
  </head>
  <body>
    <?php
      echo "<p>Hello World</p>";
      $date = date('d-m-y h:i:s');
      echo "It is {$date}!";
    ?>
  </body>
</html>