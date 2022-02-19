<?php namespace App\Libraries;

use App\Models\RecipeModel;
use App\Models\IngredientModel;
use App\Entities\Recipe;
use App\Entities\Ingredient;

class MyRecipes
{
    public $recipeModel;
    public $ingredientModel;
    private $errors;

    public function __construct()
    {
        $this->recipeModel = new RecipeModel();
        $this->ingredientModel = new IngredientModel();
        $this->errors = [];
    }

    /**
     * Define the error messages
     * @param array|string $errors
     */
    private function setErrors ($errors)
    {
        // If we receive something other than an array, convert to array
        $this->errors = is_array($errors) ? $errors : (array)$errors;
    }

    /**
     * Return the error messages
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the list of recipes
     * @param array $search
     * @return array
     */
    public function getListRecipes (array $search)
    {
        // Only get id, slug and title fields
        $this->recipeModel->select('id, slug, title');

        // If we do a text search, look in the title and instructions
        if ( ! empty($search['text']))
        {
            $this->recipeModel
                ->like('title', $search['text'])
                ->orLike('instructions', $search['text']);
        }

        // If we don't ask for a specific number of recipe per page, get the default value
        $nb_per_page = ! empty($search['nb_per_page']) ? $search['nb_per_page'] : null;

        // Add the sort order and pagination, then return the results
        $recipes = $this->recipeModel
            ->orderBy('id')
            ->paginate($nb_per_page);

        return $recipes;
    }

    /**
     * Get a recipe by its id
     * @param int $id
     * @return object|NULL
     */
    public function getRecipeById (int $id)
    {
        // Get the recipe by its id
        $recipe = $this->recipeModel->find($id);

        if ($recipe !== null)
        {
            $recipe->ingredients = $this->ingredientModel
                ->where( ['recipe_id' => $recipe->id] )
                ->orderBy('id')
                ->findAll();
        }

        return $recipe;
    }

    /**
     * Get a recipe by its slug
     * @param string $slug
     * @return object|NULL
     */
    public function getRecipeBySlug (string $slug)
    {
        // Get the recipe by its slug
        $recipe = $this->recipeModel->where('slug', $slug)->first();

        if ($recipe !== null)
        {
            $recipe->ingredients = $this->ingredientModel
                ->where( ['recipe_id' => $recipe->id] )
                ->orderBy('id')
                ->findAll();
        }

        return $recipe;
    }


    /**
     * Delete a recipe and its ingredients
     * @param int $id
     * @return bool
     */
    public function deleteRecipe (int $id): bool
    {
        // First delete the ingredients from this recipe
        if ( ! $this->ingredientModel
                ->where( ['recipe_id' => $id] )
                ->delete() )
        {
            $this->setErrors($this->ingredientModel->errors());
            return false;
        }

        // Delete the recipe
        if ( ! $this->recipeModel->delete($id))
        {
            $this->setErrors($this->recipeModel->errors());
            return false;
        }

        // Get the number of deleted rows
        $nb_delete = $this->recipeModel->db->affectedRows();
        log_message('debug', "$nb_delete recipe deleted");

        // If no rows have been deleted
        if ($nb_delete === 0)
        {
            $this->setErrors("No recipe found with id $id");
            return false;
        }

        return true;
    }

    /**
     * Save a recipe and its ingredients
     * @param int $id
     * @param array $form_data_recipe
     * @param array $form_data_ingredients
     * @return bool
     */
    public function saveRecipe (?int $id, array $form_data_recipe, array $form_data_ingredients): bool
    {
        // If we have an id, get the recipe to update
        if ( ! is_null($id))
        {
            if ( ! $recipe = $this->recipeModel->find($id) )
            {
                $this->setErrors("No recipe found");
                return false;
            }
        }
        else
        {
            $recipe = new Recipe();
        }

        // Fill in the fields of the Recipe object with the form data
        $recipe->fill($form_data_recipe);
        log_message('debug', "Recipe: " . print_r($recipe, true));

        if ($recipe->hasChanged())
        {
            // The save() function takes care of doing an INSERT or an UPDATE, depending on the case.
            if ( ! $this->recipeModel->save($recipe))
            {
                $this->setErrors($this->recipeModel->errors());
                return false;
            }
        }

        // If it is a new recipe, get its ID
        if (is_null($id))
        {
            $id = $this->recipeModel->db->insertID();
            log_message('debug', "New recipe id $id");
        }
        // If this is not a new recipe, delete its ingredients
        else
        {
            if ( ! $this->ingredientModel
                ->where( ['recipe_id' => $id] )
                ->delete() )
            {
                $this->setErrors($this->ingredientModel->errors());
                return false;
            }
        }

        // Add the new ingredients for this recipe
        foreach ($form_data_ingredients as $data_ingredient)
        {
            // Link the ingredient to the recipe
            $data_ingredient['recipe_id'] = $id;

            log_message('debug', "Data Ingredient: " . print_r($data_ingredient, true));

            // Create an Ingredient entity with the form data
            $ingredient = new Ingredient();
            $ingredient->fill($data_ingredient);

            log_message('debug', "Ingredient: " . print_r($ingredient, true));

            // Insert this ingredient in the database
            if ( ! $this->ingredientModel->save($ingredient) )
            {
                $this->setErrors($this->ingredientModel->errors());
                return false;
            }
        }

        return true;
    }
}