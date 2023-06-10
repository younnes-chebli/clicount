<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Delete Tricount</title>
</head>
<body>
    <h2 class="header">Delete    |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/edit_tricount/<?= $tricount->id ?>">Back</a>
    </h3>
    <p>Do you really want to delete this tricount(<strong><?= $tricount->title ?></strong>) and all of its dependencies ? This process cannot be undone</p>
    <form class="form" action="tricount/delete_tricount" method="post">
        <input type="text" name="tricount" value=<?= $tricount->id ?> hidden>
        <button class="form-button" type="submit"> Delete</button>
        
    </form>
</body>
</html>