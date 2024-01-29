<?php

/**
 * Class Songs
 * This is a demo Model class.
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Rini\Model;

use Rini\Core\Model;

class Song extends Model
{
    /**
     * Get all songs from database
     */
    public function getAllSongs()
    {
        return $this->db->select('SELECT id, artist, track, link FROM song');
    }

    /**
     * Add a song to database
     * TODO put this explanation into readme and remove it from here
     * Please note that it's not necessary to "clean" our input in any way. With PDO all input is escaped properly
     * automatically. We also don't use strip_tags() etc. here so we keep the input 100% original (so it's possible
     * to save HTML and JS to the database, which is a valid use case). Data will only be cleaned when putting it out
     * in the views (see the views for more info).
     * @param string $artist Artist
     * @param string $track Track
     * @param string $link Link
     */
    public function addSong($artist, $track, $link)
    {
        $this->db->insert(
            'song',
            [
                // set
                'artist' => $artist,
                'track' => $track,
                'link' => $link
            ]
        );
    }

    /**
     * Delete a song in the database
     * Please note: this is just an example! In a real application you would not simply let everybody
     * add/update/delete stuff!
     * @param int $song_id Id of song
     */
    public function deleteSong($song_id)
    {
        $this->db->delete(
            'song',
            [
                // where
                'id' => $song_id,
            ]
        );
    }

    /**
     * Get a song from database
     * @param integer $song_id
     */
    public function getSong($song_id)
    {
        $data = $this->db->select(
            'SELECT id, artist, track, link FROM song WHERE id = ? LIMIT 1',
            [
                $song_id
            ]
        );

        if(isset($data)) return $data[0];
    }

    /**
     * Update a song in database
     * // TODO put this explaination into readme and remove it from here
     * Please note that it's not necessary to "clean" our input in any way. With PDO all input is escaped properly
     * automatically. We also don't use strip_tags() etc. here so we keep the input 100% original (so it's possible
     * to save HTML and JS to the database, which is a valid use case). Data will only be cleaned when putting it out
     * in the views (see the views for more info).
     * @param string $artist Artist
     * @param string $track Track
     * @param string $link Link
     * @param int $song_id Id
     */
    public function updateSong($artist, $track, $link, $song_id)
    {
        $this->db->update(
            'song',
            [
                // set
                'artist' => $artist,
                'track' => $track,
                'link' => $link
            ],
            [
                // where
                'id' => $song_id
            ]
        );
    }

    /**
     * Get simple "stats". This is just a simple demo to show
     * how to use more than one model in a controller (see application/controller/songs.php for more)
     */
    public function getAmountOfSongs()
    {
        $data = $this->db->select('SELECT COUNT(id) AS amount_of_songs FROM song');

        if(isset($data)) return $data[0]['amount_of_songs'];
    }
}
