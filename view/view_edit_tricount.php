    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="<?= $web_root ?>">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Edit Tricount</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            let participants = <?= $participants_json ?>;
            let persons = <?= $persons_json ?>;
            let deletables = <?= $deletables_json ?>;
            let title_ok = true;
            const oldTitle = "<?= $tricount->title ?>";
            const creator = "<?= $tricount->creator ?>";
            let justvalidate_config = false;
            let titleAvailable;
            const tricountId = "<?= $tricount->id ?>";
            let edit = false;

            function debounce(fn, time) {
                var timer;

                return function() {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        fn.apply(this, arguments);
                    }, time);
                }
            }

            async function getValue() {
                return await $.getJSON("main/get_value_service");
            }


            $(async function() {
                /// justvalidate | Validation du formulaire d'encodage

                if (await getValue() === 1) {
                    justvalidate_config = true;
                    justvalidate();
                } else {
                    justvalidate_config = false;
                    $("#title").bind("input", checkTitle);
                    $("#title").bind("blur", checkTitleExists);
                    $("#description").bind("input", checkDescription);
                }

                /// Gestion dynamique des participants

                $("#participants-list").html("");
                $("#persons-list-div").hide();
                $("#persons-list-ul").removeAttr("hidden");

                await getInfos();
                display();

                /// Sweetalert

                $("#title").bind("input", edited);
                $("#description").bind("input", edited);
                $("#back").click(function(event) {
                    unsavedChanges(event);
                });
                $("#delete").click(function(event) {
                    event.preventDefault();
                    deleteTricountConfirm();
                });
            });

            ///justvalidate

            function justvalidate() {
                const validation = new JustValidate('#edit-form', {
                    validateBeforeSubmitting: true,
                    lockForm: true,
                    focusInvalidField: false,
                    successLabelCssClass: ['success'],
                    errorLabelCssClass: ['errors']
                });

                validation
                    .addField('#title', [{
                            rule: 'required',
                            errorMessage: 'Title is required'
                        },
                        {
                            rule: 'minLength',
                            value: 3,
                            errorMessage: 'Minimum 3 charachters'
                        },
                        {
                            rule: 'maxLength',
                            value: 256,
                            errorMessage: 'Maximum 256 charachters'
                        },
                        {
                            validator: function(title) {
                                return function() {
                                    return $.post("tricount/title_available_justvalidate_service_edit/", {
                                        title: title,
                                        creator: creator,
                                        oldTitle: oldTitle
                                    });
                                }
                            },
                            errorMessage: 'Title already exists for this creator'
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

                    .addField('#description', [{
                            rule: 'minLength',
                            value: 3,
                            errorMessage: 'Minimum 3 charachters'
                        },
                        {
                            rule: 'maxLength',
                            value: 1024,
                            errorMessage: 'Maximum 1024 charachters'
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

                    .onValidate(debounce(async function(event) {
                        let title = $("#title").val();
                        titleAvailable = await $.post("tricount/title_available_justvalidate_service_edit/", {
                            title: title,
                            creator: creator,
                            oldTitle: oldTitle
                        });
                        titleAvailable = JSON.parse(titleAvailable);
                        if (!titleAvailable) {
                            this.showErrors({
                                '#title': 'Title already exists for this creator'
                            });
                        }
                    }))

                    .onSuccess(function(event) {
                        if (titleAvailable)
                            event.target.submit();
                    });

            }

            /// Validation du formulaire d'encodage

            function checkTitle() {
                let ok = true;
                $("#titleError").html("");

                const title = $("#title").val().trim();
                if (title.length < 3 || title.length > 256) {
                    $("#titleError").html("Title length must be between 3 and 256");
                    ok = false;
                }

                $('#submitButton').prop('disabled', !ok);

                return ok;
            }

            async function checkTitleExists() {
                const newTitle = $("#title").val().trim();
                $("#titleError").html("");

                try {
                    const response = await $.post("tricount/title_available_justvalidate_service/", {
                        title: newTitle,
                        creator: creator
                    });

                    const data = JSON.parse(response);

                    if (!data && newTitle !== oldTitle) {
                        $("#titleError").html("Title already exists for this creator");
                        title_ok = false;
                    } else {
                        title_ok = true;
                    }

                    $('#submitButton').prop('disabled', !title_ok);
                } catch (error) {
                    throw error;
                }
            }

            function checkDescription() {
                let ok = true;
                $("#descriptionError").html("");

                const description = $("#description").val().trim();
                if (description.length > 0) {
                    if (description.length < 3 || description.length > 1024) {
                        $("#descriptionError").html("Description length must be between 3 and 1024");
                        ok = false;
                    }
                }

                $('#submitButton').prop('disabled', !ok);

                return ok;
            }

            function checkAll() {
                if (!justvalidate_config) {
                    let ok = checkTitle();
                    ok = checkDescription() && ok;
                    ok = title_ok && ok;
                    return ok;
                }
            }

            /// Sweetalert

            function edited() {
                edit = true;
            }

            function unsavedChanges(event) {
                if (edit) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Unsaved Changes',
                        html: `
                        <p>Are you sure you want to leave this form? Changes you made will not be saved.</p>
                    `,
                        icon: 'warning',
                        position: 'top',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Leave Page',
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed)
                            location.href = `tricount/tricount/${tricountId}`;
                    });
                }
            }

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
                    if (result.isConfirmed)
                        deleteParticipant(id);
                });
            }

            function deleteTricountConfirm() {
                Swal.fire({
                    title: 'Confirm Tricount Deletion',
                    html: `
                        <p>Please confirm that you want to delete the tricount</p>
                        <p>This operation can't be reversed!</p>
                    `,
                    icon: 'warning',
                    position: 'top',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete Tricount',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteTricount();
                        location.href = `tricount/index`;
                        location.reload();
                    }
                });
            }

            /// Gestion dynamique des participants

            async function getParticipants() {
                try {
                    participants = await $.getJSON("tricount/get_participants_service/<?= $tricount->id ?>");
                    display();
                } catch (error) {
                    throw error;
                }
            }

            async function getPersons() {
                try {
                    persons = await $.getJSON("tricount/get_persons_service/<?= $tricount->id ?>");
                    display();
                } catch (error) {
                    throw error;
                }
            }

            async function getDeletables() {
                try {
                    deletables = await $.getJSON("tricount/get_deletables_service/<?= $tricount->id ?>");
                    display();
                } catch (error) {
                    throw error;
                }
            }

            async function getInfos() {
                await getParticipants();
                await getPersons();
                await getDeletables();
            }

            function containsObject(obj, list) {
                for (i = 0; i < list.length; i++) {
                    if (list[i] === obj) {
                        return true;
                    }
                }

                return false;
            }

            function displayParticipants() {
                $("#participants-list").html("");
                for (const p of participants) {
                    let li = `<li>${p.full_name}`;
                    if (deletables.includes(p.id)) {
                        li += ` <a href="javascript:deleteParticipantConfirm(${p.id})"><i class="fa-solid fa-trash"></i></a>`
                    }
                    li += "</li>";
                    $("#participants-list").append(li);
                }
            }

            function displayPersons() {
                $("#persons-list-ul").html("");
                $("#persons-list-ul").addClass("flex-row");
                for (const p of persons) {
                    const li = `<li>${p.full_name}</li>`;
                    const add = `<a class="small-button" type="button" href="javascript:addParticipant(${p.id})"><i class="fa-solid fa-arrow-up fa-2xl"></i></a>`;
                    $("#persons-list-ul").append(li, add);
                }
            }

            function display() {
                displayParticipants();
                displayPersons();
                if (persons.length == 0)
                    $("#add-participant").hide();
                else
                    $("#add-participant").show();
            }

            async function addParticipant(id) {
                const idx = persons.findIndex(function(el, idx, arr) {
                    return el.id === id;
                });
                const p = persons.splice(idx, 1)[0];
                participants.push(p);
                deletables.push(p.id);
                sortParticipants();
                display();

                try {
                    await $.post("tricount/add_participant_service/<?= $tricount->id ?>", {
                        "participant_id": id
                    });
                    await getInfos();
                } catch (error) {
                    throw error;
                }
            }

            async function deleteParticipant(id) {
                const idx = participants.findIndex(function(el, idx, arr) {
                    return el.id === id;
                });
                const p = participants.splice(idx, 1)[0];
                persons.push(p);
                display();

                try {
                    await $.post("tricount/delete_participant_service/<?= $tricount->id ?>", {
                        "participant_id": id
                    });
                    await getInfos();
                } catch (error) {
                    throw error;
                }
            }

            function sortParticipants() {
                participants.sort((a, b) => {
                    if (a.full_name < b.full_name) {
                        return -1;
                    }
                });
            }

            async function deleteTricount() {
                try {
                    await $.post("tricount/delete_tricount_service/", {
                        "tricount_id": "<?= $tricount->id ?>"
                    });
                } catch (error) {
                    throw error;
                }
            }
        </script>
    </head>

    <body>
        <h2 class="header">EDIT TRICOUNT - <?= $tricount->title ?> |</h2>
        <h3 class="header">
            <a id="back" class="button hover" href="tricount/tricount/<?= $tricount->id ?>">Back</a>
            <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
        </h3>

        <form id="edit-form" onsubmit="return checkAll();" class="form" action="tricount/edit_tricount/<?= $tricount->id ?>" method="post">
            <div>
                <label>Title</label>
                <input class="input-in-div" id="title" type="text" name="title" value="<?= $title ?>">
                <p id="titleError" class="errors"></p>
                <?php if (count($title_errors) != 0) : ?>
                    <div id="errorsPHP" class="errors">
                        <ul>
                            <?php foreach ($title_errors as $title_error) : ?>
                                <li>
                                    <?= $title_error; ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <label>Description (optional)</label>
                <textarea class="input-in-div" id="description" name="description" cols="30" rows="10"><?= $description ?></textarea>
                <p id="descriptionError" class="errors"></p>
                <?php if (count($description_errors) != 0) : ?>
                    <div class="errors">
                        <ul>
                            <?php foreach ($description_errors as $description_error) : ?>
                                <li>
                                    <?= $description_error; ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <h4>Participants</h4>
            <ul id="participants-list">
                <?php foreach ($participants as $participant) : ?>
                    <li><?= $participant->full_name ?>
                        <?php if (in_array($participant, $deletables)) : ?>
                            <a href="tricount/delete_participant_confirm/<?= $participant->id ?>/<?= $tricount->id ?>"><i class="fa-solid fa-trash"></i></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h4 id="add-participant">Add participant</h4>
            <ul id="persons-list-ul" hidden></ul>
            <div id="persons-list-div" class="flex-row">
                <select class="select" name="person">
                    <option>Add Participant</option>
                    <?php foreach ($persons as $person) : ?>
                        <option value="<?= $person->id ?>"><?= $person->full_name ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="small-button" type="submit">Add</button>
            </div>

            <button id="submitButton" class="form-button" type="submit">Save</button>
        </form>
        <a id="delete" class="button hover" href="tricount/delete_tricount_confirm/<?= $tricount->id ?>">Delete This Tricount</a>

    </body>

    </html>