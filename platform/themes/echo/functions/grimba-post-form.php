<?php

/*
 * GrimbaNews — Post edit form extension.
 *
 * Adds story_cluster_id / bias_rating / is_blindspot / source_id
 * fields to the Botble admin Post form. Paired with grimba-post-hooks.php
 * which copies the submitted values from the request onto the model
 * on save (our columns aren't in Post's $fillable, so mass-assignment
 * would drop them otherwise).
 */

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Blog\Forms\PostForm;
use Illuminate\Support\Facades\DB;

app()->booted(function (): void {
    if (! class_exists(PostForm::class)) {
        return;
    }

    PostForm::extend(function (PostForm $form): PostForm {
        $sources = DB::table('news_sources')
            ->orderBy('name')
            ->get(['id', 'name', 'bias_rating'])
            ->mapWithKeys(fn ($s) => [
                $s->id => $s->name . ' — ' . match ($s->bias_rating) {
                    'left'   => 'Gauche',
                    'center' => 'Centre',
                    'right'  => 'Droite',
                    default  => 'Non classé',
                },
            ])
            ->prepend('— Aucune —', '')
            ->toArray();

        $clusters = DB::table('story_clusters')
            ->orderByDesc('id')
            ->get(['id', 'topic'])
            ->mapWithKeys(fn ($c) => [$c->id => '#' . $c->id . ' — ' . $c->topic])
            ->prepend('— Aucun —', '')
            ->toArray();

        return $form
            ->addAfter(
                'is_featured',
                'grimba_source_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Source (news_sources)')
                    ->helperText('Remplit automatiquement biais, propriété, crédibilité et nom de source.')
                    ->choices($sources)
                    ->selected((string) request()->input('source_id', optional($form->getModel())->source_id ?? ''))
                    ->toArray()
            )
            ->addAfter(
                'grimba_source_id',
                'grimba_story_cluster_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Dossier (story cluster)')
                    ->helperText('Attache cet article à un dossier pour activer la comparaison L/C/D.')
                    ->choices($clusters)
                    ->selected((string) (optional($form->getModel())->story_cluster_id ?? ''))
                    ->toArray()
            )
            ->addAfter(
                'grimba_story_cluster_id',
                'grimba_bias_rating',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Biais éditorial (override)')
                    ->helperText('Laissé à « — » : copié depuis la source. Sinon force ce biais.')
                    ->choices([
                        ''        => '— (hériter de la source) —',
                        'left'    => 'Gauche',
                        'center'  => 'Centre',
                        'right'   => 'Droite',
                        'unknown' => 'Non évalué',
                    ])
                    ->selected((string) (optional($form->getModel())->bias_rating ?? ''))
                    ->toArray()
            )
            ->addAfter(
                'grimba_bias_rating',
                'grimba_is_blindspot',
                OnOffField::class,
                TextFieldOption::make()
                    ->label('Angle mort')
                    ->helperText('Histoire couverte presque exclusivement par un seul camp.')
                    ->defaultValue((bool) (optional($form->getModel())->is_blindspot ?? false))
                    ->toArray()
            );
    });
});
