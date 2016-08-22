# icon_select_field

Drupal 8 module. Special Dropdown to indicate color of a stripe.

# Usage

After install you will find a new field Type. Use as desired.
Go to the settings page `/admin/config/content/icon_select_field` and add the needed colors.

We normally add the new field named `Icon Select` to a Paragraph called `Stripe`.
Then in the `paragraph--stripe.html.twig` we put the following:

``` twig
{% set classes = [
    'panel',
    content.field_icon_select['#items'].getValue|first.value
  ]
%}

<section {{ attributes.addClass(classes) }} id="stripe-id-{{ paragraph.id() }}">
  {{ content.field_headline }}
  {{ content.field_inner_paragraphs }}
</section>

```

# Credits

code base: [github.com/WondrousLLC/icon_select_field](https://github.com/WondrousLLC/icon_select_field/)

developed by [WONDROUS LLC](https://www.wearewondrous.com/)
