<?php
namespace App\View\Cell;

use Cake\View\Cell;

class LocationCell extends Cell
{

	/**
	 * Process children to create a cell for parent locations
	 *
	 * @param object $children
	 * @param int $depth
	 * @param object $Html helper
	 * @param object $Time helper
	 * @param object $Form helper
	 */
	public function row($children, $depth, $html, $time, $form)
	{
		$this->loadModel('Locations');
		$cell ='';
		// If there are children, don't show details
		foreach ($children->children as $child) {
			$cell .= $this->recursive($child, $depth, $html, $time, $form);
		}
		$this->set(compact('cell'));
	}

	protected function recursive($child, $depth, $html, $time, $form)
	{
		$padding = $depth * 20;// This is the paddding for each depth level
		$cell = '<tr>';
		$cell .= '<td>';
		$cell .= '<div class="btn-group btn-group-xs">';
		$cell .= '<a class="btn btn-default btn-xs" href="/locations/edit/'.$child->id.'"><i class="fa fa-fw fa-pencil"></i></a>';
		$cell .= $form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['action' => 'delete', $child->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete {0}?', h($child->name))]);
		$cell .= $form->postLink('<i class="fa fa-fw fa-arrow-down"></i>', ['action' => 'moveDown', $child->id], ['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to move down {0}?', h($child->name))]);
		$cell .= $form->postLink('<i class="fa fa-fw fa-arrow-up"></i>', ['action' => 'moveUp', $child->id], ['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to move up {0}?', h($child->name))]);
		$cell .= '</div>';
		$cell .= '</td>';
		$cell .= '<td style="padding-left:'.$padding.'px;"><i class="hidden-xs fa fa-level-up fa-rotate-90"></i> <a href="/locations/view/'.$child->id.'">'.h($child->name).'</a></td>';
		$cell .= '<td>'.h($child->description).'</td>';
		$cell .= '</tr>';

		if( count($child->children) > 0) {
			// If there are children, don't show details
			foreach ($child->children as $children) {
				$depth++;
				$cell .= $this->recursive($children, $depth, $html, $time, $form);
				$depth--;
			}
		}
		return $cell;
	}
}