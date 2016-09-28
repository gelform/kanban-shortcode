<ul class="kanban-boards">
	<?php

	$boards = Kanban_Utils::order_array_of_objects_by_property($boards, 'position', 'int');

	foreach ( $boards as $board ) : if ( $board->task_count == 0 ) continue; ?>
	<li class="kanban-board">
		<div class="kanban-board-title">
			<?php echo $board->title ?>
		</div>
		<ul class="kanban-board-statuses">
			<?php

			$statuses = Kanban_Utils::order_array_of_objects_by_property($board->statuses, 'position', 'int');

			foreach ( $statuses as $status ) : if ( !isset( $status->task_count ) || $status->task_count == 0 ) continue; ?>
			<li class="kanban-status">
				<div class="kanban-status-title">
					<?php echo $status->title ?>
				</div>
				<ul class="kanban-status-tasks">
					<?php

					$tasks = Kanban_Utils::order_array_of_objects_by_property($status->tasks, 'position', 'int');

					foreach ( $tasks as $task ) : ?>
						<li class="kanban-task">
							<div class="kanban-task-title">
								<?php echo $task->title ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</li>
			<?php endforeach; ?>
		</ul>
	</li>
	<?php endforeach ?>
</ul>