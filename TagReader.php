<?php

/*
 * TagReader.php
 * ID3 tag parser, based on id3js for node.js by 43081j@github
 * 2015 Shaun Landis <slandis@github>
 *
 * I quite liked how the original (id3js) worked in node, and wanted something
 * like it for php3, without the complexity of say, getID3. So here it is. It's
 * probably ugly, and might choke on some weird tags out there, but it works
 * for me.
 */

/*
 * Just a semi-anonymous class for storing attributes, as I like an object
 * based storage class versus a simple array.
 */
class _ {
  private $data;

  function __construct() {
    $this->data = [];
  }

  function __set($name, $value) {
    $this->data[$name] = $value;
  }

  function __get($name) {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    } else {
      return null;
    }
  }

  function __unset($name) {
    if (isset($this->data[$name])) {
      unset($this->data[$name]);
    }
  }
}

/*
 * This class contains the frame types and genre index from id3.org
 * and some basic search methods for looking up keys.
 */
class FrameInfo {
  static $Frames22 = array(
    "PIC" => "image",
    "COM" => "comments",
    "TAL" => "album",
    "TBP" => "bpm",
    "TCM" => "composer",
    "TCO" => "genre",
    "TCR" => "copyright",
    "TDA" => "date",
    "TDY" => "playlist-delay",
    "TEN" => "encoder",
    "TFT" => "file-type",
    "TIM" => "time",
    "TKE" => "initial-key",
    "TLA" => "language",
    "TLE" => "length",
    "TMT" => "media-type",
    "TOA" => "original-artist",
    "TOF" => "original-filename",
    "TOL" => "original-writer",
    "TOR" => "original-release",
    "TOT" => "original-album",
    "TP1" => "artist",
    "TP2" => "band",
    "TP3" => "conductor",
    "TP4" => "remixer",
    "TPA" => "set-part",
    "TPB" => "publisher",
    "TRC" => "isrc",
    "TRK" => "track",
    "TSI" => "size",
    "TSS" => "encoder-settings",
    "TT1" => "content-group",
    "TT2" => "title",
    "TT3" => "subtitle",
    "TXT" => "writer",
    "TYE" => "year",
    "WAF" => "url-title",
    "WAR" => "url-artist",
    "WAS" => "url-source",
    "WCM" => "url-commercial",
    "WCP" => "url-copyright",
    "WPB" => "url-publisher"
    );

  static $Frames23 = array(
    "APIC" => "image",
    "COMM" => "comments",
    "TALB" => "album",
    "TBPM" => "bpm",
    "TCOM" => "composer",
    "TCON" => "genre",
    "TCOP" => "copyright",
    "TDAT" => "date",
    "TDLY" => "playlist-delay",
    "TENC" => "encoder",
    "TEXT" => "writer",
    "TFLT" => "file-type",
    "TIME" => "time",
    "TIT1" => "content-group",
    "TIT2" => "title",
    "TIT3" => "subtitle",
    "TKEY" => "initial-key",
    "TLAN" => "language",
    "TLEN" => "length",
    "TMED" => "media-type",
    "TOAL" => "original-album",
    "TOFN" => "original-filename",
    "TOLY" => "original-writer",
    "TOPE" => "original-artist",
    "TORY" => "original-release",
    "TOWN" => "owner",
    "TPE1" => "artist",
    "TPE2" => "band",
    "TPE3" => "conductor",
    "TPE4" => "remixer",
    "TPOS" => "set-part",
    "TPUB" => "publisher",
    "TRCK" => "track",
    "TRSN" => "radio-name",
    "TRSO" => "radio-owner",
    "TSIZ" => "size",
    "TSRC" => "isrc",
    "TYER" => "year",
    "TSST" => "subtitle",
    "WCOM" => "url-commercial",
    "WCOP" => "url-legal",
    "WOAF" => "url-file",
    "WOAR" => "url-artist",
    "WOAS" => "url-source",
    "WORS" => "url-radio",
    "WPAY" => "url-payment",
    "WPUB" => "url-publisher"
  );

  static $Genres = array(
    0 => 'Blues',
    1 => 'Classic Rock',
    2 => 'Country',
    3 => 'Dance',
    4 => 'Disco',
    5 => 'Funk',
    6 => 'Grunge',
    7 => 'Hip-Hop',
    8 => 'Jazz',
    9 => 'Metal',
    10 => 'New Age',
    11 => 'Oldies',
    12 => 'Other',
    13 => 'Pop',
    14 => 'R&B',
    15 => 'Rap',
    16 => 'Reggae',
    17 => 'Rock',
    18 => 'Techno',
    19 => 'Industrial',
    20 => 'Alternative',
    21 => 'Ska',
    22 => 'Death Metal',
    23 => 'Pranks',
    24 => 'Soundtrack',
    25 => 'Euro-Techno',
    26 => 'Ambient',
    27 => 'Trip-Hop',
    28 => 'Vocal',
    29 => 'Jazz+Funk',
    30 => 'Fusion',
    31 => 'Trance',
    32 => 'Classical',
    33 => 'Instrumental',
    34 => 'Acid',
    35 => 'House',
    36 => 'Game',
    37 => 'Sound Clip',
    38 => 'Gospel',
    39 => 'Noise',
    40 => 'Alternative Rock',
    41 => 'Bass',
    42 => 'Soul',
    43 => 'Punk',
    44 => 'Space',
    45 => 'Meditative',
    46 => 'Instrumental Pop',
    47 => 'Instrumental Rock',
    48 => 'Ethnic',
    49 => 'Gothic',
    50 => 'Darkwave',
    51 => 'Techno-Industrial',
    52 => 'Electronic',
    53 => 'Pop-Folk',
    54 => 'Eurodance',
    55 => 'Dream',
    56 => 'Southern Rock',
    57 => 'Comedy',
    58 => 'Cult',
    59 => 'Gangsta',
    60 => 'Top 40',
    61 => 'Christian Rap',
    62 => 'Pop/Funk',
    63 => 'Jungle',
    64 => 'Native US',
    65 => 'Cabaret',
    66 => 'New Wave',
    67 => 'Psychadelic',
    68 => 'Rave',
    69 => 'Showtunes',
    70 => 'Trailer',
    71 => 'Lo-Fi',
    72 => 'Tribal',
    73 => 'Acid Punk',
    74 => 'Acid Jazz',
    75 => 'Polka',
    76 => 'Retro',
    77 => 'Musical',
    78 => 'Rock & Roll',
    79 => 'Hard Rock',
    80 => 'Folk',
    81 => 'Folk-Rock',
    82 => 'National Folk',
    83 => 'Swing',
    84 => 'Fast Fusion',
    85 => 'Bebob',
    86 => 'Latin',
    87 => 'Revival',
    88 => 'Celtic',
    89 => 'Bluegrass',
    90 => 'Avantgarde',
    91 => 'Gothic Rock',
    92 => 'Progressive Rock',
    93 => 'Psychedelic Rock',
    94 => 'Symphonic Rock',
    95 => 'Slow Rock',
    96 => 'Big Band',
    97 => 'Chorus',
    98 => 'Easy Listening',
    99 => 'Acoustic',
    100 => 'Humour',
    101 => 'Speech',
    102 => 'Chanson',
    103 => 'Opera',
    104 => 'Chamber Music',
    105 => 'Sonata',
    106 => 'Symphony',
    107 => 'Booty Bass',
    108 => 'Primus',
    109 => 'Porn Groove',
    110 => 'Satire',
    111 => 'Slow Jam',
    112 => 'Club',
    113 => 'Tango',
    114 => 'Samba',
    115 => 'Folklore',
    116 => 'Ballad',
    117 => 'Power Ballad',
    118 => 'Rhytmic Soul',
    119 => 'Freestyle',
    120 => 'Duet',
    121 => 'Punk Rock',
    122 => 'Drum Solo',
    123 => 'Acapella',
    124 => 'Euro-House',
    125 => 'Dance Hall',
    126 => 'Goa',
    127 => 'Drum & Bass',
    128 => 'Club-House',
    129 => 'Hardcore',
    130 => 'Terror',
    131 => 'Indie',
    132 => 'BritPop',
    133 => 'Negerpunk',
    134 => 'Polsk Punk',
    135 => 'Beat',
    136 => 'Christian Gangsta',
    137 => 'Heavy Metal',
    138 => 'Black Metal',
    139 => 'Crossover',
    140 => 'Contemporary C',
    141 => 'Christian Rock',
    142 => 'Merengue',
    143 => 'Salsa',
    144 => 'Thrash Metal',
    145 => 'Anime',
    146 => 'JPop',
    147 => 'SynthPop'
  );

  public function searchFrames23($needle) {
    foreach (array_keys(FrameInfo::$Frames23) as $key) {
      if ($key == $needle) {
        return true;
      }
    }

    return false;
  }

  public function seearchFrames22($needle) {
    foreach(array_keys(FrameInfo::$Frames22) as $key) {
      if ($key == $needle) {
        return true;
      }
    }

    return false;
  }
}

/*
 * Simple wrapping class for reading ID3 tags
 */
class TagReader {
  private $fd;
  private $path;

  function __construct($path) {
    $this->path = $path;
  }

  function __destruct() {
    if (is_resource($this->fd)) {
      fclose($this->fd);
    }
  }

  /*
   * Get all available tags that are relevant to us. Yank out ID3v1[.1] tags
   * on the go if they're spotted and then process any IDv2.[2,3] tags.
   */
  public function getInfo() {
    if (file_exists($this->path)) {
      $this->fd = fopen($this->path, 'rb');
    } else {
      return false;
    }

    $tags = new _();
    $size = filesize($this->path);
    fseek($this->fd, $size - 128);
    $header = fread($this->fd, 128);
    fseek($this->fd, 0);

    /* Process an ID3v1[.1] header if present */
    $v1 = new _();
    $v1->version = '1.0';

    if (substr($header, 0, 3) === 'TAG') {
      $v1->title = $this->getString($header, 3, 30);
      $v1->artist = $this->getString($header, 33, 30);
      $v1->album = $this->getString($header, 63, 30);
      $v1->year = $this->getString($header, 94, 4);

      if (substr($header, 125, 2) == 0) {
        $v1->comment = $this->getString($header, 97, 28);
        $v1->version = '1.1';
        $v1->track = intval($this->getUint8($header, 126));
      } else {
        $v1->comment = $this->getString($header, 97, 30);
      }

      $genre = $this->getUint8($header, 127);

      if ($genre <= count(FrameInfo::$Genres)) {
        $v1->genre = FrameInfo::$Genres[$genre];
      }
    }

    $tags->v1 = $v1;

    /* Process ID3v2.[2,3] frames if present */
    $v2 = new _();


    /* 14 bytes (10 for ID3v2 header, 4 for possible extended header size) */
    $header = fread($this->fd, 14);
    $headerSize = 10;

    if (substr($header, 0, 3) == 'ID3') {
      $major = $this->getUint8($header, 3);
      $minor = $this->getUint8($header, 4);
      $flags = $this->getUint8($header, 5);

      $v2->version = [$major, $minor];
      $v2->flags = $flags;

      /* Synchronization not supported */
      if (($flags & 0x80) !== 0) {
        return;
      }

      /* Increment the header size offset if an extended header exists */
      if (($flags & 0x40) != 0) {
        $headerSize += $this->getUint32Sync($header, 11);
      }

      $tagSize = $this->getUint32Sync($header, 6);
      fseek($this->fd, $headerSize);
      $buffer = fread($this->fd, $tagSize);
      fclose($this->fd);
      $position = 0;

      while ($position < strlen($buffer)) {
        $isFrame = true;

        for ($i = 0; $i < 3; $i++) {
          $frameBit = $this->getUint8($buffer, $position + $i);

          if (($frameBit < 0x41 || $frameBit > 0x5A) && ($frameBit < 0x30 || $frameBit > 0x39)) {
            $isFrame = false;
          }
        }

        if (!$isFrame) {
          break;
        }

        $frame = new _();
        /* < v2.3, frame ID is 3 chars, size is 3 bytes making a total size of 6 bytes */
        /* >= v2.3, frame ID is 4 chars, size is 4 bytes, flags are 2 bytes, total 10 bytes */
        if ($v2->version[0] < 3) {
          $frameSize = $this->getUint24($buffer, $position + 3) + 6;
          $slice = substr($buffer, $position, $frameSize);
          $frame = $this->parseFrame22($slice);
        } else {
          $frameSize = $this->getUint32($buffer, $position + 4) + 10;
          $slice = substr($buffer, $position, $frameSize);
          $frame = $this->parseFrame23($slice);
        }

        $position += strlen($slice);

        if ($frame)
          $v2->{$frame->tag} = $frame->value;
      }
    }

    $tags->v2 = $v2;

    $tags->artist = $v2->artist ? $v2->artist : $v1->artist;
    $tags->album = $v2->album ? $v2->album : $v1->album;
    $tags->title = $v2->title ? $v2->title : $v1->title;
    $tags->track = $v2->track ? $v2->track : $v1->track;
    $tags->year = $v2->year ? $v2->year : $v1->year;
    $tags->genre = $v2->genre ? $v2->genre : $tags->v1->genre;
    var_dump($tags);
    return $tags;
  }

  /*
   * Parse an ID3v2.3 frame, extract its type and value, and return it
   */
  private function parseFrame23($buffer) {
    $frame = new _();
    $header = new _();
    $header->id = $this->getString($buffer, 0, 4);
    $header->type = $this->getString($buffer, 0, 1);
    $header->size = $this->getUint32($buffer, 4) + 10;
    $header->flags = [ $this->getUint8($buffer, 8), $this->getUint8($buffer, 9)];

    /* No support for compressed, unsynchronized, etc frames */
    if ($header->flags[1] != 0) {
      return false;
    }

    if (!FrameInfo::searchFrames23($header->id)) {
      return false;
    }

    $frame->tag = FrameInfo::$Frames23[$header->id];

    if ($header->type == "T") {
      $encoding = $this->getUint8($buffer, 10);

      if ($encoding == 0 || $encoding == 3) {
        $frame->value = $this->getString($buffer, 11, $header->size - 11);
      } else {
        return false;
      }
    } else if ($header->type == "W") {
      $frame->value = $this->getString($buffer, 10, $header->size - 10);
    }

    return $frame;
  }

  /*
   * Extract an ID3v2.2 frame, extract its type and value, and return it
   */
  private function parseFrame22($buffer) {
    $frame = new _();
    $header = new _();
    $header->id = $this->getString($buffer, 0, 3);
    $header->type = $this->getString($buffer, 0, 1);
    $header->size = $this->getUint24($buffer, 3) + 6;

    if (!FrameInfo::searchFrames22($header->id)) {
      return false;
    }

    $frame->tag = FrameInfo::$Frames22[$header->id];

    if ($header->type == "T" || $header->type == "W") {
      $encoding = $this->getUint8($buffer, 7);

      $frame->value = $this->getString($buffer, 7, $header->size - 7);
    }

    return $frame;
  }

  /*
   * Extract an encoded field from an ID3v2 header (Bit 7 always 0)
   * This could wrap any of the getUint functions, I suppose.
   */
  private function getSync($value) {
    $out = 0;
    $mask = 0x7f000000;

    while ($mask) {
      $out >>= 1;
      $out |= $value & $mask;
      $mask >>= 8;
    }

    return $out;
  }

  private function getUint8Sync($buffer, $start) {
    return $this->getSync($this->getUint8($buffer, $start));
  }

  private function getUint32Sync($buffer, $start) {
    return $this->getSync($this->getUint32($buffer, $start));
  }

  private function getString($buffer, $start = 0, $length = 1) {
    return trim(substr($buffer, $start, $length));
  }

  private function getUint8($buffer, $start) {
    $data = unpack("C*", substr($buffer, $start, 1));

    if (is_array($data)) {
      $data = implode($data);
    }

    return intval($data);
  }

  private function getUint16($buffer, $start) {
    $long = $this->getUint8($buffer, $start + 1);
    $long += $this->getUint8($buffer, $start) << 8;
    return intval($long);
  }

  private function getUint24($buffer, $start) {
    $long = $this->getUint8($buffer, $start + 2);
    $long += $this->getUint8($buffer, $start + 1) << 8;
    $long += $this->getUint8($buffer, $start) << 16;
    return intval($long);
  }

  private function getUint32($buffer, $start) {
    $long = $this->getUint8($buffer, $start + 3);
    $long += $this->getUint8($buffer, $start + 2) << 8;
    $long += $this->getUint8($buffer, $start + 1) << 16;
    $long += $this->getUint8($buffer, $start) << 32;
    return intval($long);
  }
}

?>
