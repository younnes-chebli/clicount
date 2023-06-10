<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Add Participant</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const tricountId = "<?= $tricount->id ?>";
        let participants = [];
        <?php foreach ($participants as $participant) : ?>
            participants.push("<?= $participant->id ?>");
        <?php endforeach; ?>

        $(function() {
            for (const participant of participants) {
                $(`#${participant}`).click(function(e) {
                    e.preventDefault();
                    const link = e.target.parentNode;
                    deleteParticipantConfirm(link.id);
                });
            }
        });

        function deleteParticipantConfirm(id) {
            Swal.fire({
                title: 'Confirm Participant Deletion',
                html: `
                        <p>Please confirm that you want to delete the participant</p>
                        <p>This operation can't be reversed!</p>
                    `,
                icon: 'warning',
                position: 'top',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete Participant',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteParticipant(id);
                    location.href = `tricount/tricount/${tricountId}/ok`;
                }
            });
        }

        async function deleteParticipant(id) {
            try {
                await $.post("tricount/delete_participant_service/<?= $tricount->id ?>", {
                    "participant_id": id
                });
            } catch (error) {
                throw error;
            }
        }
    </script>
</head>

<body>
    <h2 class="header"><?= $tricount->title ?> |</h2>
    <h3 class="header">
        <a class="button hover" href="tricount/tricount/<?= $tricount->id ?>">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
    </h3>
    <?php if (count($success) != 0) : ?>
        <div class="success">
            <ul>
                <?php foreach ($success as $success_message) : ?>
                    <li>
                        <?= $success_message; ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="tricount/add_participant/<?= $tricount->id ?>" method="post">
        <h4>Participants</h4>
        <ul>
            <?php foreach ($participants as $participant) : ?>
                <li><?= $participant->full_name ?>
                    <?php if (in_array($participant, $deletables)) : ?>
                        <a id="<?= $participant->id ?>" href="tricount/delete_participant_confirm/<?= $participant->id ?>/<?= $tricount->id ?>"><i class="fa-solid fa-trash"></i></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="flex-row">
            <select class="select" name="person">
                <option>Add Participant</option>
                <?php foreach ($persons as $person) : ?>
                    <option value="<?= $person->id ?>"><?= $person->full_name ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="form-button" type="submit">Add</button>
    </form>
</body>

</html>