<?php
$query = $this->db->prepare('SELECT * FROM mvc.game where id = :id');
$query->bindParam(':id', $id, \PDO::PARAM_INT);
$query->execute();
$game = $query->fetchAll(\PDO::FETCH_ASSOC);
if(isset($game[0])) {
    $game = $game[0];
}else {
    throw new \Exception('not found game with id: ' . $id, 422);
}
return $game;