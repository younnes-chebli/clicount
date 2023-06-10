<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="./css/style.css">
    <title>Edit Operation</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
    <script src="lib/just-validate-plugin-date.production.min.js" type="text/javascript"></script>
    <!-- <script src="https://unpkg.com/browse/just-validate-plugin-date@1.2.0/dist/just-validate-plugin-date.production.min.js"></script> -->
    <script>
        const tricountParticipants = <?= $tricount_participants_json ?>;
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
            init();

            console.log();

            $("#amount-input").bind("input", refreshAmounts);
            for (const checkbox of document.querySelectorAll(".checkboxes")) {
                checkbox.addEventListener("change", refreshAmounts);
            }
            $(".weights").bind("input", refreshAmounts);

            /// sweetalert

            $("#title").bind("input", edited);
            $("#amount-input").bind("input", edited);
            $("#date").bind("change", edited);
            $("#select").bind("change", edited);
            for (const checkbox of document.querySelectorAll(".checkboxes")) {
                checkbox.addEventListener("change", edited);
            }
            for (const weight of document.querySelectorAll(".weights")) {
                weight.addEventListener("input", edited);
            }
            $("#back").click(function(event) {
                unsavedChanges(event);
            });
            $("#delete").click(function(e) {
                e.preventDefault();
                deleteOperationConfirm();
            })

            /// justvalidate

            if (await getValue() === 1)
                justvalidate();
        });

        function init() {
            $(".amounts-block").css("display", "initial");
            refreshAmounts();
        }

        function refreshAmounts() {
            let total = parseInt($("#amount-input").val());

            let totalWeights = 0;
            let weightInputs = $(".weights");
            for (const weightInput of weightInputs) {
                if (weightInput.parentElement.parentElement.children[0].children[0].checked) {
                    totalWeights += parseInt(weightInput.value);
                }

                weightInput.addEventListener("input", (e) => {
                    if (e.target.value === "0") {
                        weightInput.parentElement.parentElement.children[0].children[0].checked = false;
                    } else {
                        weightInput.parentElement.parentElement.children[0].children[0].checked = true;
                    }
                });

                weightInput.addEventListener("blur", (e) => {
                    if (e.target.value === "") {
                        e.target.value = "0";
                        weightInput.parentElement.parentElement.children[0].children[0].checked = false;
                    }
                });
            }

            for (const tricountParticipant of tricountParticipants) {
                let weight = $(`#weight-${tricountParticipant.id}`);
                let checkbox = $(`#checkbox-${tricountParticipant.id}`);
                let amount = $(`#amount-${tricountParticipant.id}`);

                if (checkbox.is(":checked")) {
                    let number = (parseInt(weight.val()) / totalWeights) * total;
                    if (!isNaN(number)) {
                        amount.html(number.toFixed(2));
                    }
                } else {
                    amount.html("0");
                }
            }
        }

        /// sweetalert

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
                        location.href = "operation/index/<?= $operation->id ?>";
                });
            }
        }

        function deleteOperationConfirm() {
            Swal.fire({
                title: 'Confirm Operation Deletion',
                html: `
                        <p>Please confirm that you want to delete the operation</p>
                        <p>This operation can't be reversed!</p>
                    `,
                icon: 'warning',
                position: 'top',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete Operation',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteOperation();
                    location.href = "tricount/tricount/<?= $operation->tricount ?>";
                }
            });
        }

        async function deleteOperation() {
            try {
                await $.post("operation/delete_operation_service/", {
                    "operation_id": "<?= $operation->id ?>"
                });
            } catch (error) {
                throw error;
            }
        }

        /// justvalidate

        function justvalidate() {
            const validation = new JustValidate('#edit-operation-form', {
                validateBeforeSubmitting: true,
                lockForm: true,
                focusInvalidField: false,
                successLabelCssClass: ['success'],
                errorLabelCssClass: ['errors']
            });

            <?php foreach ($tricount_participants as $tricount_participant) : ?>

                validation
                    .addField('#weight-<?= $tricount_participant->id ?>', [{
                            rule: 'integer',
                            errorMessage: 'Amount must be integer'
                        },
                        {
                            rule: 'minNumber',
                            value: 0,
                            errorMessage: 'Amount must be strictly positive',
                        },
                        {
                            validator: function() {
                                let sum = 0;

                                for (const weight of document.querySelectorAll(".weights")) {
                                    if (weight.parentElement.parentElement.children[0].children[0].checked)
                                        sum += Number(weight.value);
                                }

                                if (sum < 0) {
                                    return false;
                                }
                                return true;
                            },
                            errorMessage: 'Sum of weights must be strictly positive',
                        }
                    ], {
                        successMessage: 'Looks good !'
                    })

            <?php endforeach; ?>

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
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#amount-input', [{
                        rule: 'required',
                        errorMessage: 'Amount is required'
                    },
                    {
                        rule: 'number'
                    },
                    {
                        rule: 'minNumber',
                        value: 0.1
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addField('#date', [{
                        rule: 'required',
                        errorMessage: 'Date is required'
                    },
                    {
                        plugin: JustValidatePluginDate(() => ({
                            format: 'yyyy-MM-dd',
                            isBefore: new Date(new Date().getTime() + 24 * 60 * 60 * 1000).toISOString().split('T')[0]
                        })),
                        errorMessage: 'Date should be before today',
                    }
                ], {
                    successMessage: 'Looks good !'
                })

                .addRequiredGroup(
                    '#participants-group',
                    'Select at least one participant', {
                        tooltip: {
                            position: 'bottom',
                        },
                    }
                )

                .onSuccess(function(event) {
                    event.target.submit();
                });
        }
    </script>

</head>

<body>
    <h2 class="header">EDIT OPERATION - <?= $operation->title ?> |</h2>
    <h3 class="header">
        <a id="back" class="button hover" href="operation/index/<?= $operation->id ?>">Back</a>
        <a class="button hover" href="tricount/index"><?= $user->full_name . " (" . $user->role . ")" ?></a>
    </h3>

    <form id="edit-operation-form" class="form" action="operation/edit_operation/<?= $operation->id ?>" method="post">
        <div>
            <label>Title</label>
            <input class="input-in-div" id="title" type="text" name="title" value="<?= $title ?>">
            <p class="errors" id="titleError"></p>
            <?php if (count($title_errors) != 0) : ?>
                <div class="errors">
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
            <label>€</label>
            <input class="input-in-div" id="amount-input" type="text" name="amount" value="<?= $amount ?>">
            <p class="errors" id="amountError"></p>
            <?php if (count($amount_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($amount_errors as $amount_error) : ?>
                            <li>
                                <?= $amount_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label>Operation Date</label>
            <input class="input-in-div" id="date" type="date" name="operation_date" value="<?= $operation_date ?>">
            <p class="errors" id="dateError"></p>
            <?php if (count($operation_date_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($operation_date_errors as $operation_date_error) : ?>
                            <li>
                                <?= $operation_date_error; ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <label>Paid By</label>
        <select id="select" class="select" name="initiator">
            <option value="<?= $initiator->id ?>"><?= $initiator->full_name ?></option>
            <?php foreach ($tricount_participants as $tricount_participant) : ?>
                <?php if ($tricount_participant->id != $initiator->id) : ?>
                    <option value="<?= $tricount_participant->id ?>"><?= $tricount_participant->full_name ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <br>

        <div id="participants-group">
            <label>For Whom? (select at least one)</label>
            <?php foreach ($tricount_participants as $tricount_participant) : ?>
                <div class="forwhom-block">
                    <div class="participant-block">
                        <input id="checkbox-<?= $tricount_participant->id ?>" class="checkboxes" type="checkbox" name="participants[<?= $tricount_participant->id ?>]" value="<?= $tricount_participant->id ?>" <?php if (in_array($tricount_participant->id, $checked_ids)) : ?> checked <?php endif; ?>>
                        <label><?= $tricount_participant->full_name ?></label>
                        <div id="amounts-block" class="amounts-block">
                            <label id="amount-label-<?= $tricount_participant->id ?>">| Amount</label>
                            <label id="amount-<?= $tricount_participant->id ?>"></label>
                            <label id="devise">€</label>
                        </div>
                    </div>
                    <div class="weight-block">
                        <label>Weight</label>
                        <input class="weights" id="weight-<?= $tricount_participant->id ?>" min="0" type="number" name="weight[<?= $tricount_participant->id ?>]" <?php if (in_array($tricount_participant->id, $operation_participants_ids)) : ?> value="<?= $weights_by_id[$tricount_participant->id] ?>" <?php else : ?> value="1" <?php endif; ?>>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($weight_errors) != 0) : ?>
                <div class="errors">
                    <ul>
                        <li>
                            <?= $weight_errors[0]; ?>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($checkbox_error != "") : ?>
                <div class="errors">
                    <ul>
                        <li>
                            <?= $checkbox_error; ?>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <button class="form-button" type="submit">Save</button>
    </form>

    <a id="delete" class="button hover" href="operation/delete_operation_confirm/<?= $operation->id ?>">Delete This Operation</a>
</body>

</html>