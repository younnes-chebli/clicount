<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Settings</title>
</head>
<body>
    <h2 class="header">SETTINGS    |</h2>
    <h3 class="header">
        <a class="button hover" href="main/index">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")"?></a>
    </h3>

    <?php if (count($success) != 0): ?>
            <div class="success">
                <ul>
                    <?php foreach ($success as $success_message): ?>
                        <li>
                            <?= $success_message; ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
    <?php endif; ?>


    <div class="choices">
        <a class="button hover" href="main/edit_profile">Edit Profile</a>
        <a class="button hover" href="main/change_password">Change Password</a>
        <a class="button hover" href="main/logout">Logout</a>
    </div>
</body>
</html>