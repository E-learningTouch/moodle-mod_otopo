<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Otopo local functions.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Convert sessions to form data.
 *
 * @param array $sessions Sessions.
 * @return object Form data.
 */
function toform_from_sessions(array $sessions) {
    if (count($sessions) == 0) {
        return null;
    }
    $toform = new stdClass();
    $i = 0;
    foreach ($sessions as $session) {
        $toform->id[$i] = $session->id;
        $toform->name[$i] = $session->name;
        $toform->color[$i] = $session->color;
        $toform->allowsubmissionfromdate[$i] = $session->allowsubmissionfromdate;
        $toform->allowsubmissiontodate[$i] = $session->allowsubmissiontodate;
        $i++;
    }
    return $toform;
}

/**
 * Get otopo items from an otopo ID.
 *
 * @param int $otopoid Otopo ID.
 * @return array Otopo items.
 */
function get_items_from_otopo($otopoid) {
    global $DB;

    $items = [];
    $rs = $DB->get_recordset_sql(
        '
        SELECT item.id, item.name, item.color, item.ord,
            degree.id AS did, degree.name AS dname, degree.description AS ddescription, degree.grade AS dgrade, degree.ord AS dord
        FROM {otopo_item} item
        LEFT JOIN {otopo_item_degree} degree ON degree.item = item.id
        WHERE item.otopo = :otopo ORDER BY item.id, degree.ord',
        ['otopo' => $otopoid]
    );
    foreach ($rs as $record) {
        if (!array_key_exists($record->id, $items)) {
            $items[$record->id] = (object) [
                'id' => $record->id,
                'name' => $record->name,
                'color' => empty($record->color) ? '#000000' : $record->color,
                'ord' => $record->ord,
                'degrees' => [],
            ];
        }
        if ($record->did) {
            $items[$record->id]->degrees[] = (object) [
                'id' => $record->did,
                'name' => $record->dname,
                'description' => $record->ddescription,
                'grade' => $record->dgrade,
                'ord' => $record->dord,
            ];
        }
    }
    $rs->close();

    return $items;
}

/**
 * Get otopo items sorted from an otopo ID.
 *
 * @param int $otopoid Otopo ID.
 * @return array Otopo items.
 */
function get_items_sorted_from_otopo($otopoid) {
    global $DB;

    $items = [];
    $rs = $DB->get_recordset_sql(
        '
        SELECT item.id, item.name, item.color, item.ord,
            degree.id AS did, degree.name AS dname, degree.description AS ddescription, degree.grade AS dgrade, degree.ord AS dord
        FROM {otopo_item} item
        LEFT JOIN {otopo_item_degree} degree ON degree.item = item.id
        WHERE item.otopo = :otopo ORDER BY item.ord, degree.ord',
        ['otopo' => $otopoid]
    );
    foreach ($rs as $record) {
        if (!array_key_exists($record->ord, $items)) {
            $items[$record->ord] = (object) [
                'id' => $record->id,
                'name' => $record->name,
                'color' => empty($record->color) ? '#000000' : $record->color,
                'ord' => $record->ord, 'degrees' => [],
            ];
        }
        if ($record->did) {
            $items[$record->ord]->degrees[] = (object) [
                'id' => $record->did,
                'name' => $record->dname,
                'description' => $record->ddescription,
                'grade' => $record->dgrade,
                'ord' => $record->dord,
            ];
        }
    }
    $rs->close();

    return array_values($items);
}

/**
 * Perform degree checks for otopo items.
 *
 * @param array $items Items array pointer.
 * @return int Nb max degrees.
 */
function table_items(array &$items) {
    $nbrdegreesmax = 0;
    foreach ($items as $item) {
        if (count($item->degrees) > $nbrdegreesmax) {
            $nbrdegreesmax = count($item->degrees);
        }
        $item->onedegreehasdesc = false;
        foreach ($item->degrees as $index => $degree) {
            $degree->index = $index + 1;
            if (!empty($degree->description)) {
                $item->onedegreehasdesc = true;
            }
            $degree->description = nl2br($degree->description);
        }
    }

    return $nbrdegreesmax;
}

/**
 * Return otopo templates.
 *
 * @return array Otopo templates.
 */
function get_templates() {
    global $DB;
    return $DB->get_records('otopo_template', null, 'name');
}

/**
 * Copy items.
 *
 * @param int $otopoid Otopo ID.
 * @param array $items Otopo items.
 */
function copy_items(int $otopoid, array $items) {
    global $DB;

    foreach ($items as $item) {
        $degrees = $item->degrees;
        unset($item->id);
        unset($item->degrees);
        $item->otopo = $otopoid;
        $item->id = $DB->insert_record('otopo_item', $item);
        foreach ($degrees as $degree) {
            unset($degree->id);
            $degree->item = $item->id;
            $DB->insert_record('otopo_item_degree', $degree);
        }
    }
}

/**
 * Delete all otopo items.
 *
 * @param int $otopoid Otopo ID.
 */
function delete_items(int $otopoid) {
    global $DB;

    $items = $DB->get_records('otopo_item', ['otopo' => $otopoid], 'id');
    foreach ($items as $item) {
        $DB->delete_records('otopo_item_degree', ['item' => $item->id]);
    }
    $DB->delete_records('otopo_item', ['otopo' => $otopoid]);
}

/**
 * Get otopos info for a specific user.
 *
 * @param object $otopo Otopo instance.
 * @param object $user User data.
 * @return array Otopos info.
 */
function get_user_otopos(object $otopo, object $user) {
    global $DB;

    $otopos = [];

    $items = get_items_from_otopo($otopo->id);

    if (count($items) > 0) {
        [$insql, $params] = $DB->get_in_or_equal(array_keys($items));
        $params[] = intval($user->id);

        if ($otopo->session) {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND userid = ? AND session > 0";
        } else {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND userid = ? AND session < 0";
        }
        $myotopos = $DB->get_records_sql($sql, $params);

        foreach ($myotopos as $myotopo) {
            if (!array_key_exists($myotopo->item, $otopos)) {
                $otopos[$myotopo->item] = [];
            }
            $otopos[$myotopo->item][$myotopo->session] = $myotopo;
        }
    }

    return $otopos;
}

/**
 * Get otopos info users.
 *
 * @param object $otopo Otopo instance.
 * @param array $usersids Users IDs.
 * @return array Otopos info.
 */
function get_users_otopos(object $otopo, array $usersids) {
    global $DB;

    $otopos = [];

    $items = get_items_from_otopo($otopo->id);

    if (count($items) > 0 && count($usersids) > 0) {
        [$insql, $params] = $DB->get_in_or_equal(array_keys($items));
        [$insql2, $params2] = $DB->get_in_or_equal($usersids);
        $params = array_merge($params, $params2);

        if ($otopo->session) {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND userid $insql2 AND session > 0";
        } else {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND userid $insql2 AND session < 0";
        }
        $usersotopos = $DB->get_records_sql($sql, $params);

        foreach ($usersotopos as $usersotopo) {
            if (!array_key_exists($usersotopo->userid, $otopos)) {
                $otopos[$usersotopo->userid] = [];
            }
            if (!array_key_exists($usersotopo->session, $otopos[$usersotopo->userid])) {
                $otopos[$usersotopo->userid][$usersotopo->session] = [];
            }
            $otopos[$usersotopo->userid][$usersotopo->session][$usersotopo->item] = $usersotopo;
        }
    }

    return $otopos;
}

/**
 * Get users sessions for an otopo instance.
 *
 * @param object $otopo Otopo instance.
 * @return array Users sessions.
 */
function get_users_sessions_with_otopos(object $otopo) {
    global $DB;

    $otopos = [];

    $items = get_items_from_otopo($otopo->id);

    if (count($items) > 0) {
        [$insql, $params] = $DB->get_in_or_equal(array_keys($items));

        if ($otopo->session) {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND session > 0";
        } else {
            $sql = "SELECT * FROM {otopo_user_otopo} WHERE item $insql AND session < 0";
        }
        $usersotopos = $DB->get_records_sql($sql, $params);

        foreach ($usersotopos as $userotopo) {
            if (!array_key_exists($userotopo->userid, $otopos)) {
                $otopos[$userotopo->userid] = [];
            }
            if (!in_array($userotopo->session, $otopos[$userotopo->userid])) {
                $otopos[$userotopo->userid][$userotopo->session] = [
                    'id' => $userotopo->session,
                    'validated' => session_is_valid_or_closed(
                        $otopo->id,
                        (object) ['id' => $userotopo->userid],
                        $userotopo->session
                    ),
                ];
            }
            $userotopo->item = $items[$userotopo->item];
        }
    }

    return $otopos;
}

/**
 * Get graders for an otopo instance.
 *
 * @param object $otopo Otopo instance.
 * @return array Graders.
 */
function get_graders(object $otopo) {
    global $DB;

    $graders = $DB->get_records('otopo_grader', ['otopo' => $otopo->id]);

    $result = [];
    foreach ($graders as $grader) {
        if (!array_key_exists($grader->userid, $result)) {
            $result[$grader->userid] = [];
        }
        $result[$grader->userid][$grader->session] = $grader;
    }

    return $result;
}

/**
 * Convert grade to gradebook.
 *
 * @param int $user User ID.
 * @param int $grade Session grade.
 * @param string|null $comment Session comment.
 * @return array
 */
function convert_grade_to_gradebook(int $user, int $grade, ?string $comment) {
    return [
        'userid' => $user,
        'rawgrade' => $grade,
        'feedback' => strip_tags($comment),
    ];
}

/**
 * Get course participants.
 *
 * @param object $o Otopo instance (not used).
 * @param context $context Course context.
 * @return array Participants.
 */
function get_participants(object $o, context $context) {
    global $DB;

    [$esql, $params] = get_enrolled_sql($context, 'mod/otopo:fill');

    $fields = 'u.*';
    $orderby = 'u.lastname, u.firstname, u.id';
    $sql = "SELECT $fields
                      FROM {user} u
                      JOIN ($esql) je ON je.id = u.id
                     WHERE u.deleted = 0
                  ORDER BY $orderby";

    $participants = $DB->get_records_sql($sql, $params);

    return $participants;
}

/**
 * Get course participant from user ID.
 *
 * @param object $o Otopo instance.
 * @param context $context Course context.
 * @param int $userid User ID.
 * @return object Participant.
 */
function get_participant(object $o, context $context, int $userid) {
    global $DB;

    [$esql, $params] = get_enrolled_sql($context, 'mod/otopo:fill');
    $params['userid'] = $userid;

    $fields = 'u.*';
    $orderby = 'u.lastname, u.firstname, u.id';
    $sql = "SELECT $fields
                      FROM {user} u
                      JOIN ($esql) je ON je.id = u.id
                     WHERE u.deleted = 0 AND u.id = :userid
                  ORDER BY $orderby";

    $participant = $DB->get_record_sql($sql, $params);

    $otopos = get_users_sessions_with_otopos($o);

    $participant->fullname = fullname($participant, has_capability('moodle/site:viewfullnames', $context));
    if (array_key_exists($participant->id, $otopos)) {
        $key = 1;
        foreach ($otopos[$participant->id] as $otopo) {
            $otopo['key'] = $key;
            $participant->sessions[] = $otopo;
            ++$key;
        }
    }

    return $participant;
}

/**
 * Get Distribution by item.
 *
 * @param object $o Otopo instance.
 * @param array $users Users.
 * @param int|null $session Otopo session.
 * @return array Distribution.
 */
function get_distribution_by_item(object $o, array $users, ?int $session = null) {
    $otopos = get_users_otopos($o, $users);

    $lastotopos = [];

    if ($o->session) {
        if (!$session) {
            $session = get_last_session_closed($o);
            $session = $session === false ? -1 : $session->id;
        }
        if ($session) {
            foreach ($otopos as $user => $otopo) {
                if (array_key_exists($session, $otopo)) {
                    $lastotopos[$user] = $otopo[$session];
                }
            }
        } else {
            return [];
        }
    } else {
        foreach ($otopos as $user => $sessionotopo) {
            foreach (array_reverse($sessionotopo, true) as $session => $otopo) {
                if (session_is_valid($o->id, (object) ['id' => $user], $session)) {
                    $lastotopos[$user] = $otopo;
                    break;
                }
            }
        }
    }

    $distribution = [];

    if (!empty($lastotopos)) {
        $items = get_items_sorted_from_otopo($o->id);

        foreach ($items as $key1 => $item) {
            foreach ($item->degrees as $key2 => $degree) {
                if (!array_key_exists($key2, $distribution)) {
                    $distribution[$key2] = [];
                }
                if (!array_key_exists($key1, $distribution[$key2])) {
                    $distribution[$key2][$key1] = 0;
                }
                foreach ($lastotopos as $user => $otopo) {
                    if (array_key_exists($item->id, $otopo) && $otopo[$item->id]->degree == $degree->id) {
                        $distribution[$key2][$key1] += 1;
                    }
                }
            }
        }
    }

    return $distribution;
}

/**
 * Does an otopo module have an item?
 *
 * @param int $otopoid Otopo ID.
 * @return bool True if so, false otherwise.
 */
function has_otopo(int $otopoid) {
    global $DB;
    return $DB->record_exists_sql(
        '
        SELECT * FROM {otopo_user_otopo}
        INNER JOIN {otopo_item} it ON item = it.id
        WHERE it.otopo = :otopo',
        ['otopo' => $otopoid]
    );
}

/**
 * Get sessions from an otopo instance.
 *
 * @param object $otopo Otopo instance.
 * @return array Sessions.
 */
function get_sessions(object $otopo) {
    global $DB;

    $sessions = [];

    $sessionslist = $DB->get_records('otopo_session', ['otopo' => $otopo->id], 'allowsubmissionfromdate');

    foreach ($sessionslist as $session) {
        $sessions[$session->id] = $session;
    }

    return $sessions;
}

/**
 * Get last session closed from an otopo instance.
 *
 * @param object $otopo Otopo instance.
 * @return object Session.
 */
function get_last_session_closed(object $otopo) {
    global $DB;

    $date = new DateTime();

    return $DB->get_record_sql(
        'SELECT * FROM {otopo_session}
          WHERE otopo = :otopo AND allowsubmissiontodate < :now
          ORDER BY allowsubmissionfromdate DESC LIMIT 1',
        ['otopo' => $otopo->id, 'now' => $date->getTimestamp()]
    );
}

/**
 * Get opened sessions from an otopo instance.
 *
 * @param object $otopo Otopo instance.
 * @return array Sessions.
 */
function get_sessions_opened(object $otopo) {
    global $DB;

    $sessions = [];

    $date = new DateTime();
    $sessionslist = $DB->get_records_sql(
        '
        SELECT * FROM {otopo_session}
        WHERE otopo = :otopo AND allowsubmissionfromdate < :now1 AND allowsubmissiontodate > :now2
        ORDER BY allowsubmissionfromdate',
        ['otopo' => $otopo->id, 'now1' => $date->getTimestamp(), 'now2' => $date->getTimestamp()]
    );

    foreach ($sessionslist as $session) {
        $sessions[$session->id] = $session;
    }

    return $sessions;
}

/**
 * Get current session from an otopo instance for a user.
 *
 * @param object $otopo Otopo instance.
 * @param object $user User data.
 * @return array Sessions.
 */
function get_current_session(object $otopo, object $user) {
    global $DB;

    $lastvalidsession = $DB->get_record_sql(
        '
        SELECT * FROM {otopo_user_valid_session}
        WHERE userid = :user AND otopo = :otopo AND session ' . ($otopo->session ? '>' : '<') . ' 0
        ORDER BY id DESC LIMIT 1',
        ['user' => $user->id, 'otopo' => $otopo->id]
    );

    if (!$lastvalidsession) {
        $lastvalidsession = 0;
    } else {
        $lastvalidsession = $lastvalidsession->session;
    }

    if ($otopo->session) {
        $sessionso = get_sessions_opened($otopo);
        $sessions = array_keys($sessionso);
        $lastsessionid = end($sessions);
        if ($lastvalidsession) {
            if ($lastvalidsession != $lastsessionid) {
                $i = 1;
                foreach ($sessions as $id) {
                    if ($id == $lastvalidsession) {
                        return [$i, next($sessions), $sessionso[$id]];
                    }
                    ++$i;
                }
            }
        } else if (!empty($sessions)) {
            return [1, $sessions[0], $sessionso[$sessions[0]]];
        }
    } else if (abs($lastvalidsession) + 1 <= $otopo->limit_sessions) {
        return [$lastvalidsession - 1, $lastvalidsession - 1];
    }

    return null;
}

/**
 * Check if a user's session is valid.
 *
 * @param int $otopoid Otopo ID.
 * @param object $user User data.
 * @param int $session Otopo session ID.
 * @return bool True if so, false otherwise.
 */
function session_is_valid(int $otopoid, object $user, int $session) {
    global $DB;

    return $DB->record_exists('otopo_user_valid_session', ['userid' => $user->id, 'otopo' => $otopoid, 'session' => $session]);
}

/**
 * Check if a session is closed.
 *
 * @param int $session Otopo session ID.
 * @return ?bool True if so, false or null otherwise.
 */
function session_is_closed(int $session) {
    global $DB;

    if ($session > 0) {
        $date = new DateTime();
        return $DB->record_exists_sql(
            '
            SELECT * FROM {otopo_session}
            WHERE id = :id AND (allowsubmissiontodate < :now1 OR allowsubmissionfromdate > :now2)',
            ['id' => $session, 'now1' => $date->getTimestamp(), 'now2' => $date->getTimestamp()]
        );
    }
    return null;
}

/**
 * Check if a session is valid or closed.
 *
 * @param int $otopoid Otopo session ID.
 * @param object $user User data.
 * @param int $session Otopo session ID.
 * @return bool True if so, false or null otherwise.
 */
function session_is_valid_or_closed(int $otopoid, object $user, int $session) {
    global $DB;
    $valid = session_is_valid($otopoid, $user, $session);
    if ($session > 0) {
        return $valid || session_is_closed($session);
    } else if ($session < -1 && !$valid) {
        return !session_is_valid($otopoid, $user, $session + 1);
    }
    return $valid;
}

/**
 * Retrieve the last modification date of a user's session.
 *
 * @param object $otopo Otopo instance.
 * @param object $user User data.
 * @param int $session Otopo session ID.
 * @return ?int Last modification date.
 */
function last_modification_on_session(object $otopo, object $user, int $session) {
    global $DB;

    $max = $DB->get_record_sql(
        '
        SELECT MAX(lastmodificationdate) AS lastmodificationdate FROM {otopo_user_otopo}
        INNER JOIN {otopo_item} it ON item = it.id
        WHERE userid = :user AND session = :session AND it.otopo = :otopo',
        ['user' => intval($user->id), 'session' => $session, 'otopo' => $otopo->id]
    );

    if ($max && property_exists($max, 'lastmodificationdate')) {
        return $max->lastmodificationdate;
    }

    return null;
}

/**
 * Retrieve the last modification date.
 *
 * @param object $otopo Otopo instance.
 * @param object $user User data.
 * @return ?int Last modification date.
 */
function last_modification(object $otopo, object $user) {
    global $DB;

    if ($otopo->session) {
        $max = $DB->get_record_sql(
            '
            SELECT MAX(lastmodificationdate) AS lastmodificationdate
            FROM {otopo_user_otopo}
            INNER JOIN {otopo_item} it ON item = it.id WHERE userid = :user AND it.otopo = :otopo AND session > 0',
            ['user' => intval($user->id), 'otopo' => $otopo->id]
        );
    } else {
        $max = $DB->get_record_sql(
            '
            SELECT MAX(lastmodificationdate)
            FROM {otopo_user_otopo}
            INNER JOIN {otopo_item} it ON item = it.id
            WHERE userid = :user AND it.otopo = :otopo AND session < 0',
            ['user' => intval($user->id), 'otopo' => $otopo->id]
        );
    }

    if ($max && property_exists($max, 'lastmodificationdate')) {
        return $max->lastmodificationdate;
    }

    return null;
}

/**
 * Save otopo items into a CSV file.
 *
 * @param array $items Otopo items.
 * @param string $filename CSV file name.
 */
function csv_from_items(array $items, string $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    $f = fopen('php://output', 'w');

    $maxdegrees = 0;
    foreach ($items as $item) {
        if (count($item->degrees) > $maxdegrees) {
            $maxdegrees = count($item->degrees);
        }
    }

    $header = ['name', 'ord'];
    for ($i = 0; $i < $maxdegrees; $i++) {
        $header[] = 'degree' . ($i + 1) . '_name';
        $header[] = 'degree' . ($i + 1) . '_description';
        $header[] = 'degree' . ($i + 1) . '_grade';
        $header[] = 'degree' . ($i + 1) . '_ord';
    }
    fputcsv($f, $header, ',');

    foreach ($items as $item) {
        $it = (array)$item;
        $degrees = $it['degrees'];
        unset($it['id']);
        unset($it['degrees']);
        unset($it['color']);
        foreach (array_values($degrees) as $key => $degree) {
            unset($degree->id);
            foreach ($degree as $field => $value) {
                $it['degree' . $key . '_' . $field] = $value;
            }
        }
        fputcsv($f, $it, ',');
    }

    fclose($f);
}

/**
 * Prepare the data.
 *
 * @param object $o Otopo instance.
 * @param array $items Otopo items array pointer.
 * @param array $otopos Otopos array pointer.
 * @param object $user User data.
 * @return array The data.
 */
function prepare_data(object $o, array &$items, array &$otopos, object $user) {
    global $DB;

    if ($o->session) {
        $sessions = get_sessions($o);
    } else {
        $sessions = [];
        $colors = [
            "#323e70",
            "#684780",
            "#9b4e85",
            "#c7587e",
            "#e96d6e",
            "#fb8c5b",
            "#fdb14c",
            "#eed94e",
        ];

        $maxsessions = $DB->count_records('otopo_user_valid_session', [ 'otopo' => $o->id, 'userid' => $user->id ]);
        if ($maxsessions < $o->limit_sessions) {
            $maxsessions++;
        }

        $j = 0;
        for ($i = 1; $i <= $maxsessions; $i++) {
            $sessions[] = (object) ['id' => -$i, 'color' => $colors[$j]];
            $j = ($j + 1) % 8;
        }
    }

    $i = 1;
    foreach ($items as $item) {
        $item->index = $i;
        foreach ($sessions as $session) {
            if (array_key_exists($item->id, $otopos) && array_key_exists($session->id, $otopos[$item->id])) {
                $otopo = &$otopos[$item->id][$session->id];
                $rank = 0;
                foreach ($item->degrees as $key => $degree) {
                    if ($degree->id == $otopo->degree) {
                        $rank = $key + 1;
                        $otopo->degree = $degree;
                        break;
                    }
                }
                $otopo->rank = $rank;
            }
        }
        ++$i;
    }
    $i = 1;
    foreach ($sessions as $session) {
        $session->isvalidorclosed = session_is_valid_or_closed($o->id, $user, $session->id);
        $session->isvalid = session_is_valid($o->id, $user, $session->id);

        $session->index = $i;
        $session->displayname = $o->session && $session->name
            ? $session->name
            : get_string('fillautoeval', 'otopo', $session->index);

        $grader = $DB->get_record('otopo_grader', ['userid' => $user->id, 'session' => $session->id, 'otopo' => $o->id]);
        if ($grader) {
            $session->grade = $grader->grade;
            $session->comment = $grader->comment;
        } else {
            if ($o->grade > 0) {
                $session->grade = calculate_grade($otopos, $items, $session, $o->grade);
                $session->comment = "";
            }
        }
        if ($i == count($sessions)) {
            $session->last = true;
        } else {
            $session->last = false;
        }
        ++$i;
    }

    return $sessions;
}

/**
 * Calculate the grade.
 *
 * @param array $otopos Otopos.
 * @param array $items Otopo items.
 * @param object $session Otopo session.
 * @param int $grademax User data.
 * @return int The grade.
 */
function calculate_grade(array $otopos, array $items, object $session, int $grademax) {
    $num = 0;
    $den = 0;
    foreach ($items as $item) {
        if (array_key_exists($item->id, $otopos) && array_key_exists($session->id, $otopos[$item->id])) {
            $otopo = $otopos[$item->id][$session->id];
            $num += $otopo->degree->grade;
        }
        if ($item->degrees) {
            $den += $item->degrees[count($item->degrees) - 1]->grade;
        }
    }
    if ($den == 0) {
        return null;
    }
    return intval($num / $den * $grademax);
}

/**
 * Get the evolution data for a user.
 *
 * @param object $otopo Otopo instance.
 * @param array $items Otopo items.
 * @param array $itemssorted Otopo items sorted.
 * @param array $sessions Otopo sessions.
 * @param array $otopos Otopos.
 * @param object $user User data.
 * @param string $visual Visual type.
 * @param int|null $item Otopo item ID.
 * @return object The data.
 */
function get_my_evolution(
    object $otopo,
    array $items,
    array $itemssorted,
    array $sessions,
    array $otopos,
    object $user,
    string $visual = 'radar',
    ?int $item = null
) {
    $data = (object) [];

    $nbrdegreesmax = 0;
    foreach ($items as $it) {
        if (count($it->degrees) > $nbrdegreesmax) {
            $nbrdegreesmax = count($it->degrees);
        }
    }
    $data->max = $nbrdegreesmax;

    if (!$item) {
        $charts = [];
        foreach ($sessions as $session) {
            if ($visual == 'radar') {
                $charts[$session->index] = [
                    'id' => $session->index,
                    'grade' => $session->grade,
                    'comment' => $session->comment,
                    'labels' => [],
                    'fullLabel' => '',
                    'fullLabels' => [],
                    'color' => $session->color,
                    'allowsubmissionfromdate' => property_exists($session, 'allowsubmissionfromdate')
                        && $session->allowsubmissionfromdate
                        ? userdate($session->allowsubmissionfromdate, get_string('strftimedatetimeshort', 'core_langconfig'))
                        : null,
                    'allowsubmissiontodate' => property_exists($session, 'allowsubmissiontodate')
                        && $session->allowsubmissiontodate
                        ? userdate($session->allowsubmissiontodate, get_string('strftimedatetimeshort', 'core_langconfig'))
                        : null,
                    'datasets' => [[
                        'data' => [],
                        'labels' => [],
                        'label' => $session->displayname,
                        'backgroundColor' => [$session->color . 'dd'],
                        'hoverBackgroundColor' => [$session->color . '82'],
                        'borderColor' => [$session->color],
                        'borderWidth' => 1,
                    ]]];
            } else {
                $charts[$session->index] = [
                    'id' => $session->index,
                    'grade' => $session->grade,
                    'comment' => $session->comment,
                    'labels' => [],
                    'label' => $session->displayname,
                    'fullLabels' => [],
                    'color' => $session->color,
                    'datasets' => [[
                        'data' => [],
                        'labels' => [],
                        'fullLabels' => [],
                        'label' => $session->displayname,
                        'backgroundColor' => [],
                        'hoverBackgroundColor' => [],
                        'borderColor' => [],
                        'borderWidth' => 1,
                    ]]];
            }
            foreach ($itemssorted as $item) {
                $charts[$session->index]['labels'][] = strlen($item->name) > 26
                    ? (mb_substr($item->name, 0, 23) . '...')
                    : $item->name;
                $charts[$session->index]['fullLabels'][] = $item->name;
                if ($visual == 'bar') {
                    $charts[$session->index]['datasets'][0]['backgroundColor'][] = $item->color . 'dd';
                    $charts[$session->index]['datasets'][0]['hoverBackgroundColor'][] = $item->color . '82';
                    $charts[$session->index]['datasets'][0]['borderColor'][] = $item->color;
                }
                if (array_key_exists($item->id, $otopos) && array_key_exists($session->id, $otopos[$item->id])) {
                    $charts[$session->index]['datasets'][0]['data'][] = $otopos[$item->id][$session->id]->rank;
                    $charts[$session->index]['datasets'][0]['fullLabels'][] = $item->name;
                } else {
                    $charts[$session->index]['datasets'][0]['data'][] = 0;
                    $charts[$session->index]['datasets'][0]['labels'][] = '';
                }
            }
        }

        foreach ($sessions as $session) {
            $todelete = true;
            foreach ($charts[$session->index]['datasets'][0]['data'] as $value) {
                if ($value != 0) {
                    $todelete = false;
                }
            }
            if ($todelete) {
                unset($charts[$session->index]);
            }
        }

        $data->charts = array_values($charts);
    } else {
        $item = $items[$item];
        foreach ($items as $it) {
            if ($it->id == $item->id) {
                $item->index = $item->id;
                break;
            }
        }
        if ($visual == 'radar') {
            $chartitem = [
                'id' => $item->id,
                'labels' => [],
                'fullLabels' => [],
                'color' => $item->color,
                'datasets' => [[
                    'data' => [],
                    'labels' => [],
                    'label' => $item->name,
                    'backgroundColor' => [$item->color . 'dd'],
                    'hoverBackgroundColor' => [$item->color . '82'],
                    'borderColor' => [$item->color],
                    'borderWidth' => 1,
                ]]];
        } else {
            $chartitem = [
                'id' => $item->id,
                'labels' => [],
                'label' => $item->name,
                'fullLabels' => [],
                'color' => $item->color,
                'datasets' => [[
                    'data' => [],
                    'labels' => [],
                    'backgroundColor' => [],
                    'hoverBackgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1,
                ]]];
        }
        $data->chartitemdegrees = [];
        foreach ($sessions as $session) {
            // On n'ajoute pas la session en cours.
            if (!$session->isvalidorclosed) {
                continue;
            }
            $chartitem['labels'][] = $session->displayname;
            $chartitem['fullLabels'][] = $otopos[$item->id][$session->id]->degree->name;
            if ($visual == 'bar') {
                $chartitem['datasets'][0]['backgroundColor'][] = $session->color . 'dd';
                $chartitem['datasets'][0]['hoverBackgroundColor'][] = $session->color . '82';
                $chartitem['datasets'][0]['borderColor'][] = $session->color;
                $chartitem['datasets'][0]['label'] = $otopos[$item->id][$session->id]->degree->name;
            }
            if (array_key_exists($item->id, $otopos) && array_key_exists($session->id, $otopos[$item->id])) {
                $chartitem['datasets'][0]['data'][] = $otopos[$item->id][$session->id]->rank;
                $data->chartitemdegrees[] = [
                    'label' => $session->displayname,
                    'degree' => $otopos[$item->id][$session->id]->degree,
                    'color' => $session->color, 'rank' => $otopos[$item->id][$session->id]->rank,
                    'allowsubmissionfromdate' => property_exists($session, 'allowsubmissionfromdate')
                        ? userdate($session->allowsubmissionfromdate, get_string('strftimedatetimeshort', 'core_langconfig'))
                        : null,
                    'allowsubmissiontodate' => property_exists($session, 'allowsubmissiontodate')
                        ? userdate($session->allowsubmissiontodate, get_string('strftimedatetimeshort', 'core_langconfig'))
                        : null,
                    'lastmodificationonsession' => userdate(
                        last_modification_on_session($otopo, $user, $session->id),
                        get_string('strftimedatetimeshort', 'core_langconfig')
                    ),
                ];
            } else {
                $chartitem['datasets'][0]['data'][] = 0;
            }
        }
        $data->chartitem = $chartitem;
    }

    return $data;
}

/**
 * Parse a CSV file content into an array.
 *
 * @param string $csvstring CSV content.
 * @param string $delimiter CSV delimiter.
 * @param bool $skipemptylines Should skip empty lines?
 * @param bool $trimfields Should trim fields?
 * @return array Parsed content.
 */
function parse_csv(string $csvstring, string $delimiter = ",", bool $skipemptylines = true, bool $trimfields = true) {
    $enc = preg_replace('/(?<!")""/', '!!Q!!', $csvstring);
    $enc = preg_replace_callback(
        '/"(.*?)"/s',
        function ($field) {
            return urlencode(utf8_encode($field[1]));
        },
        $enc
    );
    $lines = preg_split($skipemptylines ? ($trimfields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
    return array_map(
        function ($line) use ($delimiter, $trimfields) {
            $fields = $trimfields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
            return array_map(
                function ($field) {
                    return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                },
                $fields
            );
        },
        $lines
    );
}

/**
 * Is the otopo instance open?
 *
 * @param object $otopo Otopo instance.
 * @return bool True if so, false otherwise.
 */
function is_open(object $otopo) {
    $time = time();
    return ($otopo->allowsubmissionfromdate == '0' || $otopo->allowsubmissionfromdate <= $time)
        && ($otopo->allowsubmissiontodate == '0' || $otopo->allowsubmissiontodate >= $time);
}
