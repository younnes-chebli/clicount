<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Operation</title>
</head>
<body>
    <h2 class="header">Tricount <?= $tricount->title ?> - Operation <?= $operation->title ?>    |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/tricount/<?= $tricount->id ?>">Back</a>
        <a class="button hover" href="operation/edit_operation/<?= $operation->id ?>">Edit</a>
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

    <h2 id="operation-amount-header"><?= round($operation->amount, 2) ?>€</h2>
    <p>Paid by <strong><?= $initiator_name ?></strong> on <strong><?= $formated_operation_date ?></strong></p>
    <p>For <strong><?= $participants_count ?></strong> participants<?php if($user->is_participant_to_operation($operation->id)) : ?>, including <strong>me</strong><?php endif; ?></p>
        
    <?php foreach($participants_lines as $participant) : ?>
        <?php if($participant->id == $user->id) : ?>
            <div class="participant-line">
                <p><strong><?= $participant->full_name . " (me)" ?></strong></p>
                <p><strong><?= $amounts_by_operation_participant[$participant->id]; ?></strong></p>
            </div>
        <?php else : ?>
            <div class="participant-line">
                <p><?= $participant->full_name ?></p>
                <p><?= $amounts_by_operation_participant[$participant->id]; ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="previous-next-container">
        <?php if($previous == null) : ?>
            
        <?php else : ?>
            <a class="button hover" href="operation/index/<?= $previous->id ?>">Previous</a>
        <?php endif; ?>
        <?php if($next == null) : ?>
            
        <?php else : ?>
            <a class="button hover" href="operation/index/<?= $next->id ?>">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>