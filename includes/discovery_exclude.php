<?php

function discoveryExcludeClause(): string
{
    return "
      AND u.id NOT IN (
          SELECT s.target_id FROM swipes s WHERE s.swiper_id = :exclude_swiped
          UNION
          SELECT cr.receiver_id FROM connection_requests cr
            WHERE cr.sender_id = :exclude_req_sender AND cr.status IN ('pending', 'accepted')
          UNION
          SELECT cr.sender_id FROM connection_requests cr
            WHERE cr.receiver_id = :exclude_req_receiver AND cr.status IN ('pending', 'accepted')
          UNION
          SELECT m.user2_id FROM matches m WHERE m.user1_id = :exclude_match_a
          UNION
          SELECT m.user1_id FROM matches m WHERE m.user2_id = :exclude_match_b
      )
    ";
}

function discoveryExcludeBindings(int $userId): array
{
    return [
        ':exclude_swiped' => $userId,
        ':exclude_req_sender' => $userId,
        ':exclude_req_receiver' => $userId,
        ':exclude_match_a' => $userId,
        ':exclude_match_b' => $userId,
    ];
}
