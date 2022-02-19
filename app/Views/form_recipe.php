<?php
/**
 * @var string $page_title                   The page title (automatically created by CI from the $data array)
 * @var int $max_ingredient                  Maximum number of ingredients for a recipe
 * @var App\Entities\Recipe $recipe          The recipe
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?= esc($recipe->title ?? "New recipe") ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
    integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk"
    crossorigin="anonymous">

<style type="text/css">
.title
{
    padding: 1.5rem;
}
</style>

</head>

<body>

<main role="main" class="container">

    <div class="title">
        <h1>
            <?= (isset($recipe) ? "Edit a recipe" : "New recipe") ?>
        </h1>
    </div>

<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger">
        <?= implode('<br>', session('errors')) ?>
    </div>
<?php endif; ?>

<?php if (session('message') !== null) : ?>
    <div class="alert alert-success text-center">
        <?= session('message'); ?>
    </div>
<?php endif; ?>

    <div class="container">

        <div class="mb-3">
            <?= form_open('/save' . (isset($recipe) ? "/{$recipe->id}" : "")) ?>

                <?= form_label("Title",
                               'form_title',
                               ['class' => 'form-label']) ?>

                <?= form_input('title',
                               old('title', $recipe->title ?? '', false),
                               ['class' => 'form-control mb-3']) ?>

                <?= form_label("Ingredients", '', ['class' => 'form-label']) ?>

                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Ingredient</th>
                      </tr>
                    </thead>
                    <tbody>
<?php for ($i = 0; $i < $max_ingredient; $i++): ?>
                        <tr>
                            <th scope="row"><?= ($i + 1) ?></th>
                            <td>
                                <?= form_input("ingredient_quantity_{$i}",
                                               old("ingredient_quantity_{$i}", $recipe->ingredients[$i]->quantity ?? '', false),
                                               ['class' => 'form-control']) ?>
                            </td>
                            <td>
                                <?= form_input("ingredient_name_{$i}",
                                               old("ingredient_name_{$i}", $recipe->ingredients[$i]->name ?? '', false),
                                               ['class' => 'form-control']) ?>
                            </td>
                        </tr>
<?php endfor; ?>
                  </tbody>
                </table>

                <?= form_label("Instructions",
                               'form_instruction',
                               ['class' => 'form-label']) ?>

                <?= form_textarea('instructions',
                                  old('instructions', $recipe->instructions ?? '', false),
                                  ['id' => 'form_instruction', 'class' => 'form-control mb-3']) ?>

                <?= form_submit('form_submit',
                                "Save",
                                ['class' => 'btn btn-outline-primary my-1']) ?>
            <?= form_close() ?>
        </div>

    </div>

</main>

<footer>
    <p class="text-center">&copy; 2021 <?= anchor('/', "My recipe website")?></p>
</footer>

</body>
</html>
