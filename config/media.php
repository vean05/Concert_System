<?php

/*
 * Artist photos, song and video previews, and venue photos, keyed by name.
 * Song and video previews stream from Apple; photos are free-licensed images
 * from Wikimedia Commons, and each entry records its author and licence.
 */

return json_decode(file_get_contents(database_path('data/media.json')), true);
