<?php
class Db extends PDO {
  /**
   * Définit le mode de récupération par défaut. Typiquement PDO::FETCH_ASSOC or PDO::FETCH_OBJ.
   *
   * @see http://www.php.net/manual/fr/pdo.constants.php
   */
  private $fetch_mode;

  /** 
   * Instancie un nouvel objet Db (qui hérite de PDO) qui représente une connexion à la base
   *
   * @param infos Un objet contenant les informations nécessaires pour établir une connexion à la base.
   * @param fetch_mode Le mode de récupération de données à utiliser par défaut.
   *
   * @return Retourne un objet Db en cas de succès.
   */
  public function __construct($infos, $fetch_mode = PDO::FETCH_OBJ) {
    $this->fetch_mode = $fetch_mode;

    switch ($infos->type) {
    case 'mysql':
      parent::__construct($infos->type . ':host=' . $infos->host . ';dbname=' . $infos->name,
			  $infos->user,
			  $infos->pass,
			  array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
      break;
    case 'pgsql':
      parent::__construct($infos->type . ':host=' . $infos->host . ';dbname=' . $infos->name,
			  $infos->user,
			  $infos->pass);
      break;
    case 'sqlite':
      parent::__construct($infos->type . ':' . $infos->path);
      break;
    }
  }

  /**
   * Retourne le mode de récupération utilisé par défaut.
   *
   * @return le mode de récupération (fetch mode) utilisé par défaut.
   *
   * @see http://www.php.net/manual/fr/pdostatement.setfetchmode.php
   * @see http://www.php.net/manual/fr/pdostatement.fetch.php
   * @see http://www.php.net/manual/fr/pdo.constants.php
   */
  public function getFetchMode() {
    return $this->fetch_mode;
  }

  /** 
   * Définit le mode de récupération à utiliser par défaut.
   * 
   * @param fetch_mode Le mode de récupération doit être une des constantes PDO_FETCH_*
   * 
   * @see http://www.php.net/manual/fr/pdostatement.setfetchmode.php
   * @see http://www.php.net/manual/fr/pdostatement.fetch.php
   * @see http://www.php.net/manual/fr/pdo.constants.php
   */
  function setFetchMode($fetch_mode) {
    $this->fetch_mode = $fetch_mode;
  }

  /**
   * Exécute une requête SQL et retourne un jeu de résultats.
   *
   * @param query La requête SQL à préparer et à exécuter.
   *
   * @return Retourne un jeu de résultats en tant qu'objet PDOStatement.
   *
   * @see http://www.php.net/manual/fr/pdo.query.php
   */
  public function mq($query) {
    $results = $this->query($query);
    if (!$results) {
      $infos = $this->errorInfo();
      echo "SQL ERR: " . $infos[0] . ".\nQuery was: $query\n"; //TODO: meilleure gestion des erreurs
    }
    return $results;
  }

  /** 
   * Prépare et exécute une requête SQL et retourne un jeu de résultats.
   * 
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   * @param fetch_mode Le mode de récupération à utiliser.
   * 
   * @return Retourne un jeu de résultats en tant qu'objet PDOStatement.
   * @see http://www.php.net/manual/fr/pdo.prepare.php
   * @see http://www.php.net/manual/fr/pdostatement.execute.php
   */
  public function q($query, $parameters = array(), $fetch_mode = null) {
    if (is_null($fetch_mode)) $fetch_mode = $this->fetch_mode;
    try {
      $statement = $this->prepare($query);
    }
    catch (Exception $e) {
      echo _('Erreur base de données PDO prepare :') . ' ' . $e->getMessage() . "\n";
      exit;
    }
    $statement->setFetchMode($fetch_mode);
    $r = $statement->execute($parameters);
    if ($r === false) return false;
    return $statement;
  }

  /**
   * Exécute une requête SQL et retourne la première ligne d'un jeu de résultats.
   *
   * @param query La requête SQL à préparer et à exécuter.
   *
   * @return Retourne la première ligne ce requête SQL ou FALSE en cas d'erreur.
   */
  public function mqone($query) {
    $results = $this->query($query);
    if (!$results) return false;
    return $results->fetch($this->fetch_mode);
  }

  /** 
   * Exécute une requête SQL et retourne la première ligne d'un jeu de résultats.
   *
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   * @param fetch_mode Le mode de récupération à utiliser.
   *
   * @return Retourne la première ligne ce requête SQL ou FALSE en cas d'erreur.
   */
  public function qone($query, $parameters = array(), $fetch_mode = null) {
    if (is_null($fetch_mode)) $fetch_mode = $this->fetch_mode;
    $results = $this->q($query, $parameters);
    if (!$results) return false;
    return $results->fetch($fetch_mode);
  }

  /**
   * Exécute une requête SQL et retourne les résultats en tant que tableau associatif.
   *
   * @param query La requête SQL à préparer et à exécuter.
   *
   * @return Retourne un tableau associatif où la clé correspond à la première colonne et la valeur à la deuxième colonne.
   */
  public function mqassoc($query) {
    $results = $this->query($query);
    if (!$results) return false;
    $results->setFetchMode(PDO::FETCH_NUM);

    $res = array();
    while ($data = $results->fetch())
      $res[$data[0]] = $data[1];

    return $res;
  }

  /**
   * Exécute une requête SQL et retourne les résultats en tant que tableau associatif.
   *
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   *
   * @return Retourne un tableau associatif où la clé correspond à la première colonne et la valeur à la deuxième colonne.
   */
  public function qassoc($query, $parameters = array()) {
    $results = $this->q($query, $parameters);
    if (!$results) return false;

    $res = array();
    while ($data = $results->fetch(PDO::FETCH_NUM))
      $res[$data[0]] = $data[1];

    return $res;
  }

  /**
   * Exécute une requête SQL et retourne la première colonne en tant que liste.
   *
   * @param query La requête SQL à préparer et à exécuter.
   *
   * @return Retourne un tableau indexé contenant les premiers champs de chaque ligne.
   */
  public function mqlistone($query) {
    $results = $this->query($query);
    if (!$results) return false;
    $results->setFetchMode(PDO::FETCH_NUM);

    $res = array();
    while ($data = $results->fetch())
      $res[] = $data[0];

    return $res;
  }

  /**
   * Exécute une requête SQL et retourne la première colonne en tant que liste.
   *
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   *
   * @return Retourne un tableau indexé contenant les premiers champs de chaque ligne.
   */
  public function qlistone($query, $parameters = array()) {
    $results = $this->q($query, $parameters);
    if (!$results) return false;

    $res = array();
    while ($data = $results->fetch(PDO::FETCH_NUM))
      $res[] = $data[0];

    return $res;    
  }

  /**
   * Exécute une requête SQL et retourne le jeu de résultats en tant que liste.
   *
   * @param query La requête SQL à préparer et à exécuter.
   *
   * @return Retourne un tableau indexé contenant les champs de chaque ligne.
   */
  public function mqlist($query) {
    $results = $this->query($query);
    if (!$results) return false;

    return $results->fetchAll($this->fetch_mode);
  }

  /**
   * Exécute une requête SQL et retourne le jeu de résultats en tant que liste.
   *
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   * @param fetch_mode Le mode de récupération à utiliser.
   *
   * @return Retourne un tableau indexé contenant les champs de chaque ligne.
   *
   * @see http://www.php.net/manual/fr/pdostatement.fetchall.php
   */
  public function qlist($query, $parameters = array(), $fetch_mode = null) {
    if (is_null($fetch_mode)) $fetch_mode = $this->fetch_mode;
    $results = $this->q($query, $parameters);
    if (!$results) return false;

    return $results->fetchAll($fetch_mode);
  }

  /**
   * Exécute une requête SQL et retourne le premier champ de la première ligne.
   *
   * @param query La requête SQL à préparer et à exécuter.
   * @param parameters Un tableau de valeurs avec autant d'éléments qu'il y a de paramètres à associer dans la requête SQL qui sera exécutée.
   *
   * @return Retourne le premier champ de la première ligne du résultat de la requête.
   */
  public function qonefield($query, $parameters = array()) {
    $results = $this->q($query, $parameters);
    if (!$results) return false;
    $data = $results->fetch(PDO::FETCH_NUM);
    return $data[0];
  }

  public function mqonefield($query) {
    return $this->qonefield($query);
  }

  /**
   * Protège une chaîne pour l'utiliser dans une requête SQL
   *
   * @param str La chaîne à protéger.
   * @param type Le type de données, par défaut une chaine de caractères.
   *
   * @return Retourne une chaîne protégée ou FALSE.
   *
   * @see http://www.php.net/manual/fr/pdo.quote.php
   */
  public function mquote($str, $type = PDO::PARAM_STR) {
    return $this->quote($str, $type);
  }
}
