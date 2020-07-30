<?php
/**
 * Class that registers WPCLI command for Login.
 * 
 * Command Develop:
 * wp eval-file bin/cli.php create_login --skip-wordpress
 *
 * @package EightshiftLibs\Login
 */

namespace EightshiftLibs\Login;

use EightshiftLibs\Cli\AbstractCli;

/**
 * Class LoginCli
 */
class LoginCli extends AbstractCli {

  /**
   * Output dir relative path.
   */
  const OUTPUT_DIR = 'src/login';

  /**
   * Output class name.
   */
  const CLASS_NAME = 'Login';

  /**
   * Get WPCLI command name
   *
   * @return string
   */
  public function get_command_name() : string {
    return 'create_login';
  }

  /**
   * Get WPCLI trigger class name.
   *
   * @return string
   */
  public function get_class_name() : string {
    return LoginCli::class;
  }

  /**
   * Get WPCLI command doc.
   *
   * @return string
   */
  public function get_doc() : array {
    return [
      'shortdesc' => 'Generates Login class file.',
    ];
  }

  public function __invoke( array $args, array $assoc_args ) {

    // Read the template contents, and replace the placeholders with provided variables.
    $class = $this->get_example_template( __DIR__, static::CLASS_NAME );

    // Replace stuff in file.
    $class = $this->rename_class_name( static::CLASS_NAME, $class );
    $class = $this->rename_namespace( $assoc_args, $class );
    $class = $this->rename_use( $assoc_args, $class );

    // Output final class to new file/folder and finish.
    $this->output_write( static::OUTPUT_DIR, static::CLASS_NAME, $class );
  }
}
