<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Balance</title>
</head>
<body>
    <h2 class="header">BALANCE - <?= $tricount->title ?>    |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/tricount/<?= $tricount->id ?>">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")"?></a>
    </h3>
    <div class="balance-container">
        <?php foreach($balances as $cle => $valeur) : ?>
            <?php if($valeur < 0) : ?>
                <div class="balance-line row-reverse">
                <label><?= $cle ?></label>
                    <div class="w3-container w3-red w3-center" style="width: 50%"><span class="black"><?= $valeur ?>€</span></div>
                </div>
            <?php else : ?>
                <div class="balance-line">
                <label class="text-end"><?= $cle ?></label>
                    <div class="w3-container w3-green w3-center" style="width: 50%"><span class="black"><?= $valeur ?>€</span></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>