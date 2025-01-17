<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet" type="text/css">
</head>

<body style="font-family: 'Poppins', Arial, sans-serif">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px;">
                <table class="content" width="600" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1px solid #cccccc;">
                    <!-- Header -->
                    <tr>
                        <td class="header" style="background-color: #345C72; padding: 40px; text-align: center; color: white; font-size: 24px;">
                        <img src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . $data['company_logo']); ?>" style="height: 100px;" alt="">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body" style="padding: 40px; text-align: left; font-size: 16px; line-height: 1.6;">
                        <?php echo e(__('Hello, Sir!')); ?> <br>
                        <?php echo e(__('This test mail from')); ?> <?php echo e(env('APP_NAME')); ?>.
                        </td>
                    </tr>

                    <!-- Call to action Button -->
                    <tr>
                        <td style="padding: 40px 40px 40px 40px; text-align: center;">
                            <!-- CTA Button -->
                            <table cellspacing="0" cellpadding="0" style="margin: auto;">
                                <tr>
                                    <td align="center" style="background-color: #345C72; padding: 10px 20px; border-radius: 5px;">
                                        <a href="<?php echo e(route('home')); ?>" target="_blank" style="color: #ffffff; text-decoration: none; font-weight: bold;"><?php echo e(__('Go Back')); ?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="footer" style="background-color: #333333; padding: 40px; text-align: center; color: white; font-size: 14px;">
                        Copyright &copy; <?php echo e(date('Y')); ?> | <?php echo e(env('APP_NAME')); ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/email/test_email_notification.blade.php ENDPATH**/ ?>