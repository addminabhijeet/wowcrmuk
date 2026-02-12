<a href="{{ route('admin.notifications') }}" 
   class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between"
   style="
        text-decoration:none;
        color:inherit;
        background:rgba(255,255,255,0.08);
        backdrop-filter:blur(22px) saturate(260%);
        -webkit-backdrop-filter:blur(22px) saturate(260%);
        border-radius:18px;
        border:1px solid rgba(255,255,255,0.22);
        box-shadow:
            inset 0 0 22px rgba(255,255,255,0.12);
        transition:0.28s ease;
        cursor:pointer;
   "
   onmouseover="this.style.background='rgba(255,255,255,0.15)'; this.style.border='1px solid rgba(255,255,255,0.34)'; this.style.boxShadow='inset 0 0 26px rgba(255,255,255,0.18)';"
   onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.border='1px solid rgba(255,255,255,0.22)'; this.style.boxShadow='inset 0 0 22px rgba(255,255,255,0.12)';"
>

    <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">

        <div>
            <h6 class="text-md fw-semibold mb-4" 
                style="
                    margin:0;
                    padding:0;
                    line-height:1.55;
                    font-weight:600;
                    letter-spacing:0.2px;
                    color:#1a1a1a;
                    text-shadow:0 1px 2px rgba(255,255,255,0.4);
                ">
                {!! nl2br(e($msg)) !!}
            </h6>
        </div>
    </div>

    <span class="text-sm text-secondary-light flex-shrink-0"
          style="
                opacity:0.8;
                padding:4px 10px;
                border-radius:10px;
                background:rgba(255,255,255,0.12);
                backdrop-filter:blur(10px);
                -webkit-backdrop-filter:blur(10px);
                border:1px solid rgba(255,255,255,0.22);
                font-weight:500;
          ">
        Just now
    </span>

</a>
