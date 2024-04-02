<p>Dear <b><?= $mail_data['user']->name ?></b></p>
<br/>
<p>
    Your password on Mypime-electrica system was changed successfull:
    <br/><br/>
    <b>Login ID:</b><?= $mail_data['user']->username ?> or <?= $mail_data['user']->email ?>
    <br/>
    <b>Password: </b> <?= $mail_data['new_password'] ?>
</p>