<div class="col-xl-3 col-lg-4">
    <div class="profile-user-left sticky-side-div">
        <div class="profile-user-info">
            <div class="profile-user-top ">
                <div class="profile-user-img">
                    <img src="{{show_image(file_path()['profile']['user']['path'].'/'.$user->image,file_path()['profile']['user']['size'])}}" alt="{{auth_user('web')->name}}" />
                </div>
                <div class="profile-user-name flex-grow-1">
                    <h5>
                        {{auth_user('web')->name}}
                    </h5>
                    <a href="javascript:void(0)"> {{auth_user('web')->email}} </a>
                </div>
            </div>

               <div class="nav flex-column nav-pills gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    

                    <a href="{{route('user.dashboard')}}"  class="nav-link account-tab {{request()->routeIs('user.dashboard') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Dashboard")}}
                        </span>
                    </a>


                    <a href="{{route('user.digital.order.list')}}"  class="nav-link account-tab {{request()->routeIs('user.digital.order.list') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Digital Orders")}}
                        </span>
                    </a>

                    
                

                    <a href="{{route('user.wishlist.item')}}"  class="nav-link account-tab {{request()->routeIs('user.wishlist.item') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Wishlist")}}
                        </span>
                    </a>


                    <a href="{{route('cart.view')}}"  class="nav-link account-tab {{request()->routeIs('cart.view') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Cart")}}
                        </span>
                    </a>


                    <a href="{{route('user.reviews')}}"  class="nav-link account-tab {{request()->routeIs('user.reviews') ? 'active' : ''}}  ">
                        <span>
                            {{translate("My Reviews")}}
                        </span>
                    </a>


                    <a href="{{route('user.support.ticket.index')}}"  class="nav-link account-tab {{request()->routeIs('user.support.ticket.index') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Support Ticket")}}
                        </span>
                    </a>


                    <a href="{{route('user.profile')}}"  class="nav-link account-tab {{request()->routeIs('user.profile') ? 'active' : ''}}  ">
                        <span>
                            {{translate("Manage Profile")}}
                        </span>
                    </a>


                    <a href="{{route("logout")}}" class="nav-link account-tab mt-2">
                        {{translate("Log Out")}}
                    </a>
            </div>

        </div>
    </div>
</div>