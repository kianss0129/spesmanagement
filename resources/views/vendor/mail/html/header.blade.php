@props(['url'])

<tr>
<td class="header" style="text-align: center; padding: 25px 0;">
    <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">

        <img 
            src="{{ asset('images/spes-logo.png') }}"
            class="logo"
            style="
                height: 90px;
                width: auto;
                display: block;
                margin: 0 auto;
            "
        >

        <h2 style="
            margin-top: 10px;
            color: #1f3a63;
            font-family: Arial, sans-serif;
            font-weight: bold;
        ">
            SPES Management System
        </h2>

    </a>
</td>
</tr>
