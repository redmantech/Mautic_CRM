<div id="taskTab">
    <ul class="tasks">
    <?php foreach ($tasks as $task) {
        echo $view->render('CustomCrmBundle:Task:task.html.php', array(
            'task' => $task
        ));
    } ?>
    </ul>
</div>