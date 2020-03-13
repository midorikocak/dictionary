
<section class="results">
    <h2>Titles</h2>
    <ol>
        <?php foreach($titles as $title):  ?>
            <li>
                <p><a href="/titles/<?=$title['id']?>"><?=$title['title']?></a></p>
            </li>
        <?php endforeach;  ?>
    </ol>
</section>
