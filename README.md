# yapep_utils
Utilities for the YapepBase framework

## DbTableGenerator
Small batch script that's capable of generating table descriptor classes from an existing database table.
The script can either work with the connection parameters defined as command line switches, or with a connection
configured in the configuration system.

Run it with `batch/DbTableGenerator/batch/generate_table.php`. Use the `--help` switch for an explanation of available
switches.