# asgardcms-icredibanco (BTN)

### Requires

	- Icommerce Module
	- IcommerceCredibanco Module

### Data Configuration

	- Same that IcommerceCredibanco Module

### Include on Views (FrontEnd)

	- Add like a Button:

		Example: @include('icredibanco::frontend.form')

	- Add like on Menu:

		- Create the MENU ITEM in BackEnd
		- Add in the header.blade file (Or the one you want):
		
			@include('icredibanco::frontend.form-menu',['idMENU' => 'menu','nLI' => 8])

		idMENU = Menu identifier
		nLI = Position of the MENU ITEM where you want to add the event (li:nth-child)

		=============================================
		Example Code Html FrontEnd Partials Header:
			
			<div id = "menu>
				@include('partials.navigation')
			</div>
		=============================================
	