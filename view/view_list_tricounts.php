<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Tricounts</title>
</head>
<body>
    <h2 class="header">TRICOUNT    |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")"?></a>
    </h3>
    <div class="your-tricounts">
        <h2>Your tricounts</h2>
        <a class="button hover" href="tricount/add_tricount">Add Tricount</a>
    </div>
    <div class="list">
        <?php foreach($tricounts as $tricount) : ?>
            <a href="tricount/tricount/<?= $tricount->id ?>">
                <div class="item hover">
                    <div class="item-left">
                        <p class="tricount-title"><?= $tricount->title ?></p>
                        <p class="tricount-description">
                            <?php if($tricount->description == null || $tricount->description == "NULL"): ?>
                                <span class="grey">no description</span>
                            <?php else : ?>
                                <?= $tricount->description ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="item-right">
                        <?php if(count($tricount->get_participants()) == 1) : ?>
                            <p class="tricount-with">you're alone</p>
                        <?php elseif(count($tricount->get_participants()) == 2) : ?>
                            <p class="tricount-with">With <?= count($tricount->get_participants()) - 1 ?> friend</p>
                        <?php else : ?>
                            <p class="tricount-with">With <?= count($tricount->get_participants()) - 1 ?> friends</p>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach ?>
    </div>
    <footer>
        <a class="button hover" href="main/settings"><i class="fa-solid fa-gear"></i></a>
    </footer>
</body>
</html>