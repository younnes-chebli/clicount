<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Delete Operation</title>
</head>
<body>
    <h2 class="header">DELETE OPERATION - <?= $operation->title ?>    |</h2>
    <h3 class="header">
        <a class="button hover" href="operation/edit_operation/<?= $operation->id ?>">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")"?></a>
    </h3>

    <h1>Are You Sure ?</h1>
    <p>Do you really want to delete operation <strong><?= $operation->title ?></strong> and all of its dependencies ?</p>
    <p>This process cannot be undone</p>
    <form class="form" action="operation/delete_operation" method="post">
        <input type="text" name="param" value="<?= $operation->id ?>" hidden>
        <button class="form-button" type="submit">YES</button>
    </form>

    <a class="button hover" href="operation/edit_operation/<?= $operation->id ?>">CANCEL</a>
</body>
</html>