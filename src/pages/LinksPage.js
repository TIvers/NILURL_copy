import React, { useEffect, useState } from "react";
import LinkPageMainPart from "../components/MainPage/LinkPageMainPart";
import HeaderLinksPage from "../components/Global/HeaderLinksPage";
import HeaderLinksPageFree from "../components/Global/HeaderLinksPageFree";
import HeaderLinksPageBase from "../components/Global/HeaderLinksPageBase"; 
// import transition from "../LogicComp/Transition";
import useAuth from "../pages/useAuth";
import { useNavigate } from "react-router-dom";
import { usePremium } from '../LogicComp/DataProvider';
import { Helmet } from 'react-helmet';

const LinksPage = () => {
    const navigate = useNavigate();
    const { isLoggedIn, isLoading, isRedirected, setIsRedirected } = useAuth();
    const { isPremium} = usePremium();
    
    const [userStatus, setUserStatus] = useState(''); 
    useEffect(() => {
            setUserStatus(isPremium);
        if (!isLoading && !isLoggedIn && !isRedirected) {
            setIsRedirected(true);
            navigate('/login');
        }
    }, [isLoading, isLoggedIn, navigate, isRedirected, setIsRedirected,isPremium]);

    if (isLoading) {
        return <div></div>;
    }

    const renderHeader = () => {
        switch (userStatus) {
            case 'free':
                return <HeaderLinksPageFree />;
            case 'premium':
                return <HeaderLinksPage />;
            case 'base':
                return <HeaderLinksPageBase />;
            default:
                return <HeaderLinksPageFree />;
        }
    };

    return (
        <div>
            <Helmet>
                <title>Ссылки</title>
            </Helmet>
            {renderHeader()}
            <LinkPageMainPart />
        </div>
    );
};

export default LinksPage;
