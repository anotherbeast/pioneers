PHPMailer is required for email sending in contact.php.

To install PHPMailer:
1. Download the latest PHPMailer release from https://github.com/PHPMailer/PHPMailer/releases
2. Extract the 'src' folder from the PHPMailer archive into this directory so you have:
   pioneers/api/PHPMailer/src/PHPMailer.php (and other files)
3. No Composer is required for this setup.
4. Your contact.php will autoload PHPMailer from this location.

If you need help, see: https://github.com/PHPMailer/PHPMailer#installation
