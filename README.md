# Multiple Field Type

*anomaly.field_type.multiple*

A field type for creating many-to-many relationships with multiple selection support in PyroCMS.

## Description

The multiple field type provides an intuitive interface for selecting multiple related entries from another stream or model. It creates a `belongsToMany` relationship and supports various display modes including tags, checkboxes, and lookup tables.

## Features

- **Multiple Selection**: Select many related entries at once
- **Multiple Input Modes**: Tags, checkboxes, lookup, or search
- **Many-to-Many Relationships**: Proper database relationship handling
- **Pre-defined Handlers**: Built-in handlers for common relationships
- **Custom Queries**: Filter available options
- **Sortable**: Drag and drop to reorder selections
- **Value Tables**: Interactive table interface for managing relationships
- **Caching**: Performance optimization for large datasets

## Configuration

### Basic Configuration

```php
'categories' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => \App\Category\CategoryModel::class,
    ],
],
```

### Configuration Options

#### `related` (required)
The related stream or model class.

```php
// Stream notation
'related' => 'example.module.tags'

// Full model class
'related' => \Anomaly\PostsModule\Tag\TagModel::class

// Pre-defined handlers
'related' => 'related'
```

#### `mode` (default: 'tags')
The input interface mode.

```php
'mode' => 'tags'       // Tag-style interface
'mode' => 'checkboxes' // Checkbox list
'mode' => 'lookup'     // Modal lookup table
'mode' => 'search'     // AJAX search
```

#### `handler`
Custom options handler.

```php
'handler' => 'App\\Example\\CustomOptionsHandler@handle'
```

#### `query`
Customize the query for available options.

```php
'query' => function ($query) {
    return $query->where('published', true)->orderBy('name');
}
```

#### `value_table`
Custom table builder for selected values display.

```php
'value_table' => \App\\Example\\CustomValueTableBuilder::class
```

#### `min`
Minimum number of required selections.

```php
'min' => 2 // At least 2 selections required
```

#### `max`
Maximum number of allowed selections.

```php
'max' => 5 // Maximum 5 selections
```

## Input Modes

### Tags Mode (Default)
Tag-style interface with easy add/remove.

```php
'tags' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => \App\Tag\TagModel::class,
        'mode'    => 'tags',
    ],
],
```

### Checkboxes Mode
Checkbox list for all available options.

```php
'categories' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'categories',
        'mode'    => 'checkboxes',
    ],
],
```

### Lookup Mode
Modal window with searchable table.

```php
'products' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => \App\Product\ProductModel::class,
        'mode'    => 'lookup',
    ],
],
```

### Search Mode
AJAX-powered search for large datasets.

```php
'users' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'users',
        'mode'    => 'search',
    ],
],
```

## Usage Examples

### Blog Post Tags

```php
protected $fields = [
    'tags' => [
        'type'   => 'anomaly.field_type.multiple',
        'config' => [
            'related' => 'posts.module.tags',
            'mode'    => 'tags',
        ],
    ],
];
```

### Product Categories

```php
'categories' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => \App\Category\CategoryModel::class,
        'mode'    => 'checkboxes',
        'min'     => 1,
        'max'     => 5,
    ],
],
```

### User Roles

```php
'roles' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'users.module.roles',
        'mode'    => 'checkboxes',
    ],
],
```

### Related Products

```php
'related_products' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => \App\Product\ProductModel::class,
        'mode'    => 'lookup',
        'query'   => function ($query) {
            return $query->where('published', true)
                ->where('stock', '>', 0);
        },
    ],
],
```

### Custom Options Handler

```php
'featured_posts' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'posts',
        'handler' => 'App\\Post\\FeaturedPostsHandler@handle',
    ],
],
```

Handler example:

```php
namespace App\Post;

use Anomaly\MultipleFieldType\MultipleFieldType;

class FeaturedPostsHandler
{
    public function handle(MultipleFieldType $fieldType)
    {
        $options = [];
        
        $posts = PostModel::where('featured', true)
            ->where('published', true)
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get();
            
        foreach ($posts as $post) {
            $options[$post->id] = $post->title;
        }
        
        $fieldType->setOptions($options);
    }
}
```

## Accessing Values

### Basic Output

```php
// Get all related entries
$tags = $entry->tags; // Collection of related entries

// Loop through related entries
foreach ($entry->tags as $tag) {
    echo $tag->name;
}

// Count related entries
$count = $entry->tags->count();

// Check if has specific entry
if ($entry->tags->contains($tagId)) {
    // Has this tag
}

// Get IDs only
$tagIds = $entry->tags->pluck('id')->toArray();
```

### In Templates

```twig
{# Loop through related entries #}
{% if entry.tags|length %}
    <div class="tags">
        {% for tag in entry.tags %}
            <span class="badge badge-primary">{{ tag.name }}</span>
        {% endfor %}
    </div>
{% endif %}

{# As comma-separated list #}
{{ entry.tags|join(', ', 'name') }}

{# Count #}
<p>{{ entry.tags|length }} tags</p>

{# Check if has tag #}
{% if entry.tags|filter(tag => tag.slug == 'important')|length %}
    <span class="badge badge-danger">Important</span>
{% endif %}
```

### Advanced Template Usage

```twig
{# Categories with links #}
<nav class="breadcrumb">
    {% for category in entry.categories %}
        <a href="{{ url_route('categories.show', [category.slug]) }}">
            {{ category.name }}
        </a>
        {% if not loop.last %} / {% endif %}
    {% endfor %}
</nav>

{# Products grid #}
<div class="related-products">
    {% for product in entry.related_products %}
        <div class="product-card">
            <img src="{{ product.image.url }}" alt="{{ product.name }}">
            <h4>{{ product.name }}</h4>
            <p>{{ product.price|currency }}</p>
        </div>
    {% endfor %}
</div>
```

### Presenter Output

```php
// Access via presenter
$tags = $entry->present()->tags;

// Get formatted list
echo $entry->present()->tags->list(); // "Tag1, Tag2, Tag3"

// Get as links
echo $entry->present()->tags->links();
```

## Setting Values

### By IDs Array

```php
$entry->tags = [1, 2, 3];
$entry->save();

// Or use sync
$entry->tags()->sync([1, 2, 3]);
```

### By Model Instances

```php
$tags = TagModel::whereIn('slug', ['php', 'laravel', 'pyrocms'])->get();
$entry->tags()->sync($tags->pluck('id'));
```

### Adding/Removing Individual Items

```php
// Add single item
$entry->tags()->attach($tagId);

// Remove single item
$entry->tags()->detach($tagId);

// Add multiple
$entry->tags()->attach([1, 2, 3]);

// Remove all
$entry->tags()->detach();
```

## Database Structure

Creates a pivot table for many-to-many relationships:

```php
// Pivot table: {parent_table}_{field_name}
// Columns: {parent}_id, {related}_id, sort_order

Schema::create('posts_tags', function (Blueprint $table) {
    $table->integer('post_id');
    $table->integer('tag_id');
    $table->integer('sort_order')->default(0);
    $table->primary(['post_id', 'tag_id']);
});
```

## Validation

```php
'tags' => [
    'type'  => 'anomaly.field_type.multiple',
    'rules' => [
        'required',
        'array',
        'min:2', // At least 2 selections
        'max:10', // Maximum 10 selections
    ],
    'config' => [
        'related' => 'tags',
    ],
],
```

## Common Use Cases

### Content Tagging
```php
'tags' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => ['related' => 'tags', 'mode' => 'tags'],
],
```

### Product Categorization
```php
'categories' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'categories',
        'mode'    => 'checkboxes',
        'min'     => 1,
    ],
],
```

### User Permissions/Roles
```php
'roles' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => ['related' => 'roles', 'mode' => 'checkboxes'],
],
```

### Related Content
```php
'related_articles' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => [
        'related' => 'articles',
        'mode'    => 'lookup',
        'max'     => 5,
    ],
],
```

### Product Features
```php
'features' => [
    'type'   => 'anomaly.field_type.multiple',
    'config' => ['related' => 'features', 'mode' => 'checkboxes'],
],
```

## Best Practices

### Choose the Right Mode
- **Tags**: Best for frequently changing selections, user-friendly
- **Checkboxes**: Good for < 20 options, all visible at once
- **Lookup**: Best for 20-10,000 options, searchable table
- **Search**: Best for > 10,000 options, AJAX search

### Performance Optimization
- Use eager loading: `Entry::with('tags')->get()`
- Add database indexes on pivot table
- Limit query results with `max` config
- Cache dynamic options
- Use search mode for large datasets

### Data Integrity
- Set appropriate min/max limits
- Use validation rules
- Consider cascading deletes on pivot table
- Index foreign keys

## Advanced Usage

### Pivot Table Data

```php
// Access pivot data
foreach ($entry->tags as $tag) {
    echo $tag->pivot->sort_order;
    echo $tag->pivot->created_at;
}

// Sync with pivot data
$entry->tags()->sync([
    1 => ['sort_order' => 1],
    2 => ['sort_order' => 2],
    3 => ['sort_order' => 3],
]);
```

### Eager Loading with Constraints

```php
$entries = EntryModel::with(['tags' => function ($query) {
    $query->where('active', true)->orderBy('name');
}])->get();
```

### Conditional Options

```php
'config' => [
    'related' => 'products',
    'query'   => function ($query) use ($entry) {
        return $query->where('category_id', $entry->category_id)
            ->where('id', '!=', $entry->id);
    },
]
```

### Custom Pivot Model

```php
namespace App\Post;

class PostTag extends Pivot
{
    protected $table = 'posts_tags';
    
    public $timestamps = true;
}

// In Post model
public function tags()
{
    return $this->belongsToMany(Tag::class)
        ->using(PostTag::class)
        ->withTimestamps();
}
```

## Troubleshooting

### Values Not Saving
- Verify pivot table exists
- Check foreign key constraints
- Ensure IDs are valid
- Check validation rules

### Performance Issues
- Use search mode for large datasets
- Eager load relationships
- Add database indexes
- Cache options

### Options Not Loading
- Verify related model/stream exists
- Check query configuration
- Clear cache
- Check handler errors

## API Methods

```php
// Get related model
$fieldType->getRelatedModel();

// Get relation
$fieldType->getRelation();

// Get options
$fieldType->getOptions();

// Set options
$fieldType->setOptions($options);

// Get selected IDs
$fieldType->getIds();
```

## Requirements

- PyroCMS 3.x
- Anomaly Streams Platform ^1.10

## License

This field type is open-sourced software licensed under the [MIT license](LICENSE.md).

## Authors

- **PyroCMS, Inc.** - [Website](http://pyrocms.com/)
- **Ryan Thompson** - [Website](http://ryanthepyro.com/)

