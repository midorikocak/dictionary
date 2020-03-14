
<section class="results">
    <ol>
        <?php foreach($titles as $title):  ?>
            <li>
                <p><a href="/titles/<?=$title['id']?>"><?=$title['title']?></a></p>
            </li>
        <?php endforeach;  ?>
    </ol>
</section>
