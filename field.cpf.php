<?php defined('BASEPATH') or exit('No direct script access allowed');

class Field_cpf
{
	public $field_type_slug = "cpf";	
	public $db_col_type = "bigint(11)";
	public $version = "1.0.0";
	public $author = array(
							'name' => 'Sandro Boçon',
							'github' => 'https://github.com/sandrobocon'
					);
	
	/**
 	 * Output form input
	 *
	 * @access public
	 * @param array
	 * @return string
	 */
	public function form_output($data)
	{
		$options['name']   	= $data['form_slug'];
		$options['id']   	= $data['form_slug'];
		
		if ( ! empty($data['value'])) {
			// Format '000.000.000-00'
			$cpf = preg_replace( '/[^0-9]/', '', $data['value'] );
			$cpf = str_pad($cpf, 11, "0", STR_PAD_LEFT);
			$cpf = substr($cpf, 0,3).'.'.substr($cpf, 3,3).'.'.substr($cpf, 6,3).'-'.substr($cpf, 9,2);
			$options['value']  	= $cpf;
		} else {
			$options['value']  	= null;
		}
	
		return form_input($options);
	}
	
	public function pre_save($input, $field, $stream, $row_id)
	{
		if (empty($input)) {
			$input = null;
		} else {
			$input = preg_replace( '/[^0-9]/', '', $input );
		}

		return $input;
	}

	public function pre_output($input, $data)
	{
		// Format '000.000.000-00'
		if ( ! empty($input)) {
			$cpf = str_pad($input, 11, "0", STR_PAD_LEFT);
			$input = substr($cpf, 0,3).'.'.substr($cpf, 3,3).'.'.substr($cpf, 6,3).'-'.substr($cpf, 9,2);
		}

		return $input;
	}
	
	public function validate( $value, $mode, $field )
	{			
		// Remove everything that is not number 
		$cpf = preg_replace( '/[^0-9]/', '', $value );
			
		if(strlen($cpf)> 11) 
			return $this->CI->lang->line('streams:cpf.too_long');
		
		if( ! is_numeric($cpf))
			return $this->CI->lang->line('streams:cpf.invalid_format');
		
		// Turn it to string with exactly 11 digits (insert 0's on left)
		$cpf = str_pad($cpf, 11, "0", STR_PAD_LEFT);

		// Validações CPF
		// Numericamente validos, mas são invalidos
		switch ($cpf) {
			case '00000000000':
			case '11111111111':
			case '22222222222':
			case '33333333333':
			case '44444444444':
			case '55555555555':
			case '66666666666':
			case '77777777777':
			case '88888888888':
			case '99999999999':
				return $this->CI->lang->line('streams:cpf.invalid');
				break;
		}

		/*
		| Formato : NNNNNNNNN-DD	(9-2 digitos)
		| Onde: NNNNNNNNN - Número do CPF
		| D - Dígito Verificador
		| 
		| a) Multiplica os algarismos pelos seus respectivos pesos, conforme a seguir :
		| 		 
		| Pesos: 10,9,8,7,6,5,4,3,2
		| Número: NNNNNNNNN
		| 
		| Calculo
		| DD (posição do dígito)
		*/
		$peso = array(1=>10, 2=>9, 3=>8, 4=>7, 5=>6, 6=>5, 7=>4, 8=>3, 9=>2);
		// Armazena na array $digito os digitos separados
		for ($i=1; $i < 12; $i++) { 
			$digito[$i] = substr($cpf, ($i -1) ,1);
			$digito[$i] = intval($digito[$i]);
		}
		// Multiplica os algarismos pelos seus respectivos pesos
		for ($i=1; $i < 10; $i++) { 
			$produto[$i] = $peso[$i] * $digito[$i];
		}
		/*
		| b) Soma todos os produtos obtidos no item "a".
		| Soma = X1 + X2 + X3 + X4 + X5 + X6 + X7 + X8 + X9
		*/
		$soma = 0;
		for ($i=1; $i < 10; $i++) { 
			$soma += $produto[$i];
		}
		/*
		| c) Divide por 11 o resultado obtido no item "b".
		*/
		$restodivisao1 = $soma % 11;
		/*
		| restodivisao1 = Soma % 11
		| 
		|  Caso o resto da divisão seja menor que 2, o nosso primeiro dígito verificador 
		|  deve ser 0 (zero), caso contrário subtrai-se o valor obtido de 11
		|  sendo assim o dígito verificador 
		*/
		if ($restodivisao1 < 2) {
			if ($digito[10] != 0) {
				return $this->CI->lang->line('streams:cpf.invalid');
			}
		} else {
			$verificador1 = 11 - $restodivisao1;
			if ($verificador1 < 0) {
				$verificador1 = (-1 * $verificador1);
			}

			if ($verificador1 != $digito[10]) {
				return $this->CI->lang->line('streams:cpf.invalid');
			}
		}
		/*
		| Calculo para segundo digito verificador
		| d) Multiplica os algarismos pelos seus respectivos pesos incluido o primeiro
		|  digito verificador, conforme a seguir :
		| 		 
		| Pesos: 11,10,9,8,7,6,5,4,3,2
		| Número: NNNNNNNNN D
		*/
		$peso2 = array(1=>11, 2=>10, 3=>9, 4=>8, 5=>7, 6=>6, 7=>5, 8=>4, 9=>3, 10=>2);

		// Multiplica os algarismos pelos seus respectivos pesos
		for ($i=1; $i < 11; $i++) { 
			$produto2[$i] = $peso2[$i] * $digito[$i];
		}
		/*
		| e) Soma todos os produtos obtidos no item "d".
		| Soma = X1 + X2 + X3 + X4 + X5 + X6 + X7 + X8 + X9 + X10
		*/
		$soma2 = 0;
		for ($i=1; $i < 11; $i++) { 
			$soma2 += $produto2[$i];
		}
		/*
		| c) Divide por 11 o resultado obtido no item "b".
		*/
		$restodivisao2 = $soma2 % 11;
		/*
		| restodivisao1 = Soma % 11
		| 
		|  Caso o resto da divisão seja menor que 2, o nosso primeiro dígito verificador 
		|  deve ser 0 (zero), caso contrário subtrai-se o valor obtido de 11
		|  sendo assim o dígito verificador 
		*/
		if ($restodivisao2 < 2) {
			if ($digito[11] != 0) {
				return $this->CI->lang->line('streams:cpf.invalid');
			}
		} else {
			$verificador2 = 11 - $restodivisao2;
			if ($verificador2 < 0) {
				$verificador2 = (-1 * $verificador2);
			}
			if ($verificador2 != $digito[11]) {
				return $this->CI->lang->line('streams:cpf.invalid');
			}
		}
		// Caso não retorne erro em nenhuma das condições acima retorna true
		return true;	
	}
}
