<section class="results">
    <h2><?= $title['title'] ?></h2>
    <ol>
        <?php foreach ($title['entries'] ?? [] as $entry): ?>
            <li>
                <p>
                    <?= $entry['content'] ?>
                    <?php if ($isLogged): ?>
                    <a href="/entries/<?= $entry['id'] ?>/edit">Edit</a> <a class="delete"
                        href="/entries/<?= $entry['id'] ?>/delete">Delete</a></p>
                    <?php endif; ?>
            </li>

        <?php endforeach; ?>
    </ol>
    <?php if ($isLogged): ?>
        <a href="/titles/<?= $title['id'] ?>/addEntry">Add Entry</a>
    <?php endif; ?>
</section>
