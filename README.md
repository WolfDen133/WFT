# WFT
### Hello, and welcome to **WFT**.

This is the offical recode of my old plugin WFT, witch had some issues. Overall I was not happy with the result, the code was messy, it was all in one file, there was useless and in-efficent code, with a hard api to use, so decided to recode it.

I have added features, because I felt the plugin needed them, and removed features, as some of the features where not needed.

## Features added

- Optional display identifier
- More Tags
- Per-Player Texts
- Packet Texts over Entity
- More efficent and pretty code

Display identifier: You can now turn on or off the option to see the texts unique identifer (Much like texter, but optional)

More tags: Added many more tags for better, they are also player spasific.

Per-Player texts: Players will now get a spasific text depending on what tag you use.

Packets: The old plugin used entitys, putting more load on the server, whereas this plugin uses packets, therefor creating a more lightweight plugin.

Efficent and pretty code: The code in this plugin is WAY more efficent and clean that the old plugin. The code is also spaced out in multiple files.

## Features

- Refresh timer
- Tags for various things
- Lots of useful commands
- Form or command line options
- Help subcommand for new people 
- Powerful fast and efficient code
- Extensive customizability

## Example
![Info](https://i.imgur.com/7UZQGWR.png)

## Commands

Master command is ft|wft.master

Subcommand | Permission | Description | Aliases
---------|----------|----------|---------
`wft`|`wft.command.use`|The master command| `ft`
`add`| ~ |Add a new ft| `spawn`, `summon`, `new`, `make`, `create`, `c`, `a`
`remove`| ~ |Remove a existing ft| `break`, `delete`, `bye`, `d`, `r`, 
`edit`| ~ |Edit an existiong ft| `e`, `change`
`tp`| ~ |Teleport to an ft| `teleportto`, `tpto`, `goto`, `teleport`
`tphere`| ~ |Teleport a ft to you| `teleporthere`, `movehere`, `bringhere`, `tph`, `move`
`list`| ~ |See a list of the current fts| `see`, `all` 
`help`| ~ |So you can get some in-game help| `stuck`, `h`, `?`

## Tags

  Tag|Description
  -|-
  `#`|New line
  `&`|Use for color codes (same as `§`)
  `{NAME}`|Players real name
  `{REAL_NAME}`|Players real name
  `{DISPLAY_NAME}`|Players display name (often nick plugins use display name)
  `{PING}`|Players Current Ping
  `{MAX_PLAYERS}`|Maximum players that can be on the server
  `{ONLINE_PLAYERS}`|Currently online player count
  `{X}`|Players X Position
  `{Y}`|Players Y Position
  `{Z}`|Players Z Position
  `{REAL_TPS}`|Current server tps
  `{TPS}`|Average server tps
  `{REAL_LOAD}`|Current server load
  `{LOAD}`|Average server load
  `{LEVEL_NAME}`|Players current level name
  `{LEVEL_FOLDER}`|Players current level folder name
  `{LEVEL_PLAYERS}`|Players current level player count
  `{CONNECTION_IP}`|The IP address that the player connected from
  `{SERVER_IP}`|The servers IP address
  `{TIME}`|Current server time (Customisable in config)
  `{DATE}`|Current server date (Customisable in config)

### Create your own
  Coming soon...
  
## API

Example:

Import the classes
```php

use WolfDen133\WFT\WFT;
use WolfDen133\WFT\Texts\FloatingText;

```
Creating the text
```php
// Creation and regirstration
$floatingText = new FloatingText(new Position($x, $y, $z, $level), $name, $text);
WFT::getAPI()->registerText($floatingText);

// Spawning
WFT::getAPI()::spawnTo($player, $floatingText);
// or
WFT::getAPI()::spawnToAll($floatingText);
```

Changing the ft's text
```php
// Changing the text
$floatingText->setText($text);

// Pushing the update
WFT::getAPI()::respawnTo($player, $floatingText);
// or 
WFT::getAPI()::respawnToAll($floatingText);
```
Thats it! The rest is handled by the plugin.
  --------
